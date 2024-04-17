<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Expectation\Factories;

use StrictPhp\StrictMock\Testing\Assert\Entities\AssertFileStateEntity;
use StrictPhp\StrictMock\Testing\Entities\ObjectEntity;
use StrictPhp\StrictMock\Testing\Factories\PhpFileFactory;
use ReflectionClass;
use ReflectionMethod;

final class ExpectationObjectEntityFactory
{
    public function __construct(
        public readonly PhpFileFactory $phpFileFactory,
    ) {
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public function create(
        AssertFileStateEntity $assertFileState,
        ReflectionClass $class,
        ReflectionMethod $method
    ): ObjectEntity
    {
        $methodName = ($assertFileState->oneParameterOneExpectation && count(
            $class->getMethods(ReflectionMethod::IS_PUBLIC)
        ) === 1) ? '' : ucfirst($method->getName());

        $className = $class->getShortName() . $methodName . 'Expectation';

        return new ObjectEntity(
            $assertFileState->object->exportSetup,
            $className,
            $this->phpFileFactory->create(),
        );
    }
}
