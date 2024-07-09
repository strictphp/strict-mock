<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Expectation\Actions;

use Closure;
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
use StrictPhp\StrictMock\Testing\Constants\StubConstants;
use StrictPhp\StrictMock\Testing\Entities\PhpDocEntity;
use StrictPhp\StrictMock\Testing\Enums\PhpType;
use StrictPhp\StrictMock\Testing\Expectation\AbstractExpectation;
use StrictPhp\StrictMock\Testing\Expectation\Entities\ExpectationObjectEntity;
use StrictPhp\StrictMock\Testing\Helpers\Php;

final class ExpectationFileContentAction
{
    public const HookProperty = '_hook';


    /**
     * @param string $classShortName
     */
    public function execute(
        string $classShortName,
        PhpNamespace $phpNamespace,
        ReflectionMethod $method,
        PhpDocEntity $phpDoc,
    ): ExpectationObjectEntity {
        $shortClassName = self::shortClassName($classShortName, $method);

        $expectationClass = self::appendExpectationClass($phpNamespace, $shortClassName);

        $constructor = $expectationClass
            ->addMethod('__construct');

        $returnType = $method->getReturnType();
        if ($returnType !== null &&
            ($returnType instanceof ReflectionNamedType === false || self::canReturnExpectation($returnType)) ||
            $phpDoc->returnType === PhpType::Mixed) {
            $constructorParameter = $constructor
                ->addPromotedParameter('return'); // do not set readonly

            $this->setParameterType($returnType, $constructorParameter, $phpNamespace);
        }

        $parameterTypes = [];
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $constructorParameter = $constructor
                ->addPromotedParameter($parameter->getName())
                ->setReadOnly();
            $parameterTypes[] = $this->setParameterType($parameter->getType(), $constructorParameter, $phpNamespace);
            $this->setParameterDefaultValue($classShortName, $parameter, $constructorParameter);
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

        return new ExpectationObjectEntity($expectationClass, $constructor);
    }

    private static function shortClassName(
        string $classShortName,
        ReflectionMethod $method
    ): string {
        return $classShortName
            . ucfirst($method->getName())
            . 'Expectation';
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
        string $classShortName,
        ReflectionParameter $parameter,
        PromotedParameter $constructorParameter,
    ): void {
        if ($parameter->isDefaultValueAvailable() === false) {
            return;
        }

        if ($parameter->isDefaultValueConstant()) {
            $constant = $parameter->getDefaultValueConstantName();
            // Ensure that constants are from global scope
            $constantLiteral = new Literal(str_replace(
                ['parent', 'self', 'static'],
                $classShortName,
                (string) $constant
            ));
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

    private static function appendExpectationClass(PhpNamespace $namespace, string $className)
    {
        return $namespace->addClass($className)
            ->setFinal()
            ->setExtends(AbstractExpectation::class)
            ->addComment('@internal');
    }
}
