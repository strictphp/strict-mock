<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Expectation\Factories;

use LaraStrict\StrictMock\Testing\Entities\ObjectEntity;
use LaraStrict\StrictMock\Testing\Factories\PhpFileFactory;
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
    public function create(ObjectEntity $assertObject, ReflectionClass $class, ReflectionMethod $method): ObjectEntity
    {
        $className = $class->getShortName() . ucfirst($method->getName()) . 'Expectation';

        return new ObjectEntity(
            $assertObject->exportSetup,
            $className,
            $this->phpFileFactory->create(),
        );
    }

}
