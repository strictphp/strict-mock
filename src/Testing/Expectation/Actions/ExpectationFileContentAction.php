<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Expectation\Actions;

use Closure;
use LaraStrict\StrictMock\Testing\Actions\AddUseByTypeAction;
use LaraStrict\StrictMock\Testing\Actions\WritePhpFileAction;
use LaraStrict\StrictMock\Testing\Assert\Entities\AssertFileStateEntity;
use LaraStrict\StrictMock\Testing\Constants\StubConstants;
use LaraStrict\StrictMock\Testing\Entities\ObjectEntity;
use LaraStrict\StrictMock\Testing\Entities\PhpDocEntity;
use LaraStrict\StrictMock\Testing\Enums\PhpType;
use LaraStrict\StrictMock\Testing\Expectation\AbstractExpectation;
use LaraStrict\StrictMock\Testing\Expectation\Factories\ExpectationObjectEntityFactory;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PromotedParameter;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

final class ExpectationFileContentAction
{
    public const HookProperty = '_hook';

    public function __construct(
        private readonly ExpectationObjectEntityFactory $expectationObjectEntityFactory,
        private readonly WritePhpFileAction $writePhpFileAction,
        private readonly AddUseByTypeAction $addUseByTypeAction,
    ) {
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public function execute(
        ReflectionClass $class,
        AssertFileStateEntity $assertFileState,
        ReflectionMethod $method,
        PhpDocEntity $phpDoc,
    ): ObjectEntity {
        $expectationObject = $this->expectationObjectEntityFactory->create($assertFileState->object, $class, $method);
        $parameters = $method->getParameters();

        $namespace = $expectationObject->content->addNamespace($expectationObject->exportSetup->namespace)
            ->addUse(Closure::class)
            ->addUse(AbstractExpectation::class);
        $class = $namespace->addClass($expectationObject->shortClassName)
            ->setExtends(AbstractExpectation::class);

        $constructor = $class
            ->setFinal()
            ->addMethod('__construct');

        $returnType = $method->getReturnType();
        if ($returnType !== null &&
            ($returnType instanceof ReflectionNamedType === false || self::canReturnExpectation($returnType)) ||
            $phpDoc->returnType === PhpType::Mixed) {
            $constructorParameter = $constructor
                ->addPromotedParameter('return')
                ->setReadOnly();

            $this->addUseByTypeAction->execute($namespace, $returnType);
            $this->setParameterType($returnType, $constructorParameter);
        }

        $parameterTypes = [];
        foreach ($parameters as $parameter) {
            $constructorParameter = $constructor
                ->addPromotedParameter($parameter->name)
                ->setReadOnly();
            $this->addUseByTypeAction->execute($namespace, $parameter->getType());
            $parameterTypes[] = $this->setParameterType($parameter->getType(), $constructorParameter);
            $this->setParameterDefaultValue($parameter, $constructorParameter);
        }
        $parameterTypes[] = 'self';

        $constructor
            ->addPromotedParameter(self::HookProperty)
            ->setReadOnly()
            ->setType(Closure::class)
            ->setNullable()
            ->setDefaultValue(null);

        $constructor->addComment(
            sprintf('@param %s(%s):void|null $%s', Closure::class, implode(',', $parameterTypes), self::HookProperty),
        );

        $this->writePhpFileAction->execute($expectationObject);

        return $expectationObject;
    }

    private static function canReturnExpectation(ReflectionNamedType $returnType): bool
    {
        return $returnType->getName() !== PhpType::Void->value
            && $returnType->getName() !== PhpType::Self->value
            && $returnType->getName() !== PhpType::Static->value;
    }

    private function setParameterType(
        ReflectionType|ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $type,
        PromotedParameter $constructorParameter,
    ): string {
        $proposedType = '';

        $allowNull = false;
        $mapToName = static function (ReflectionType $type) use (&$allowNull): ?string {
            if ($type instanceof ReflectionNamedType) {
                $name = $type->getName();
                if ($name === 'null') {
                    $allowNull = false;
                }

                // Fix global namespace
                if (class_exists($name)) {
                    return '\\' . $name;
                }

                return $name;
            }

            return null;
        };

        // TODO move to separate action and test with unit test
        if ($type instanceof ReflectionNamedType) {
            $allowNull = $type->allowsNull();
            $proposedType = $type->getName();

            if (class_exists($proposedType)) {
                // Fix global namespace
                $proposedType = '\\' . $proposedType;
            }

            $constructorParameter->setNullable($type->allowsNull());
        } else if ($type instanceof ReflectionUnionType) {
            $allowNull = $type->allowsNull();
            $proposedType = implode('|', array_filter(array_map($mapToName, $type->getTypes())));
        } else if ($type instanceof ReflectionIntersectionType) {
            $allowNull = $type->allowsNull();
            $proposedType = implode('&', array_filter(array_map($mapToName, $type->getTypes())));
        }

        if ($proposedType === '') {
            $proposedType = 'mixed';
        }

        if ($allowNull) {
            $constructorParameter->setNullable($allowNull);
        }

        // Callable not supported in property
        if ($proposedType === 'callable') {
            $proposedType = '\Closure';
        }

        $constructorParameter->setType($proposedType);

        return $proposedType;
    }

    private function setParameterDefaultValue(
        ReflectionParameter $parameter,
        PromotedParameter $constructorParameter,
    ): void {
        if ($parameter->isDefaultValueAvailable() === false) {
            return;
        }

        if ($parameter->isDefaultValueConstant()) {
            $constant = $parameter->getDefaultValueConstantName();
            // Ensure that constants are from global scope
            $constantLiteral = new Literal(StubConstants::NameSpaceSeparator . $constant);
            $constructorParameter->setDefaultValue($constantLiteral);

            return;
        }

        $defaultValue = $parameter->getDefaultValue();

        if (is_object($defaultValue)) {
            $objectLiteral = new Literal(
                'new ' . StubConstants::NameSpaceSeparator . $defaultValue::class . '(/* unknown */)',
            );
            $constructorParameter->setDefaultValue($objectLiteral);
        } else {
            $constructorParameter->setDefaultValue($defaultValue);
        }
    }

}
