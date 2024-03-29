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
use LaraStrict\StrictMock\Testing\Expectation\Entities\ExpectationFileEntity;
use LaraStrict\StrictMock\Testing\Expectation\Factories\ExpectationObjectEntityFactory;
use LaraStrict\StrictMock\Testing\Helpers\Php;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpNamespace;
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
    ): ExpectationFileEntity {
        $expectationObject = $this->expectationObjectEntityFactory->create($assertFileState, $class, $method);

        $namespace = $expectationObject->content->addNamespace($expectationObject->exportSetup->namespace)
            ->addUse(Closure::class)
            ->addUse(AbstractExpectation::class);

        $expectationClass = $namespace->addClass($expectationObject->shortClassName)
            ->setFinal()
            ->setExtends(AbstractExpectation::class);

        $constructor = $expectationClass
            ->addMethod('__construct');

        $returnType = $method->getReturnType();
        if ($returnType !== null &&
            ($returnType instanceof ReflectionNamedType === false || self::canReturnExpectation($returnType)) ||
            $phpDoc->returnType === PhpType::Mixed) {
            $constructorParameter = $constructor
                ->addPromotedParameter('return')
                ->setReadOnly();

            $this->addUseByTypeAction->execute($namespace, $returnType);
            $this->setParameterType($returnType, $constructorParameter, $namespace);
        }

        $parameterTypes = [];
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $constructorParameter = $constructor
                ->addPromotedParameter($parameter->getName())
                ->setReadOnly();
            $this->addUseByTypeAction->execute($namespace, $parameter->getType());
            $parameterTypes[] = $this->setParameterType($parameter->getType(), $constructorParameter, $namespace);
            $this->setParameterDefaultValue($class, $parameter, $constructorParameter, $namespace);
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

        return new ExpectationFileEntity($expectationObject, $constructor);
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
        PhpNamespace $namespace,
    ): string {
        $proposedTypeShort = null;
        $proposedType = '';

        $allowNull = false;
        $mapToName = function (ReflectionType $type) use (&$allowNull, $namespace): ?string {
            if ($type instanceof ReflectionNamedType) {
                $name = $type->getName();
                if ($name === 'null') {
                    $allowNull = false;
                }

                // Fix global namespace
                if (class_exists($name)) {
                    $reflection = new ReflectionClass($name);
                    $this->addUseByTypeAction->execute($namespace, $reflection);
                    $name = $reflection->getShortName();
                }

                return $name;
            }

            return null;
        };

        // TODO move to separate action and test with unit test
        if ($type instanceof ReflectionNamedType) {
            $allowNull = $type->allowsNull();
            $proposedType = $type->getName();

            if (Php::existClassInterfaceEnum($proposedType)) {
                // Fix global namespace
                $reflection = new ReflectionClass($proposedType);
                $this->addUseByTypeAction->execute($namespace, $reflection);
                $proposedType = $reflection->getName();
                $proposedTypeShort = $reflection->getShortName();
            }

            $constructorParameter->setNullable($type->allowsNull());
        } elseif ($type instanceof ReflectionUnionType) {
            $allowNull = $type->allowsNull();
            $proposedType = implode('|', array_filter(array_map($mapToName, $type->getTypes())));
        } elseif ($type instanceof ReflectionIntersectionType) {
            $allowNull = $type->allowsNull();
            $proposedType = implode('&', array_filter(array_map($mapToName, $type->getTypes())));
        }

        if ($proposedType === '') {
            $proposedType = 'mixed';
        }

        // Callable not supported in property
        if ($proposedType === 'callable') {
            $proposedType = Closure::class;
        }

        $suffix = '';
        if ($allowNull) {
            $constructorParameter->setNullable($allowNull);
            if ($proposedType !== '') {
                $suffix = '|null';
            }
        }
        $constructorParameter->setType($proposedType);

        return ($proposedTypeShort ?? $proposedType) . $suffix;
    }

    private function setParameterDefaultValue(
        ReflectionClass $class,
        ReflectionParameter $parameter,
        PromotedParameter $constructorParameter,
        PhpNamespace $namespace,
    ): void {
        if ($parameter->isDefaultValueAvailable() === false) {
            return;
        }

        if ($parameter->isDefaultValueConstant()) {
            $constant = $parameter->getDefaultValueConstantName();
            // Ensure that constants are from global scope
            $this->addUseByTypeAction->execute($namespace, $class);
            $constantLiteral = new Literal(str_replace(['parent', 'self', 'static'], $class->getShortName(), $constant));
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
