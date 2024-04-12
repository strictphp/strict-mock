<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Assert\Factories;

use LaraStrict\StrictMock\Testing\Entities\FileSetupEntity;
use LaraStrict\StrictMock\Testing\Entities\ObjectEntity;
use LaraStrict\StrictMock\Testing\Factories\PhpFileFactory;
use LaraStrict\StrictMock\Testing\Transformers\ReflectionClassToFileSetupEntity;
use ReflectionClass;

final class AssertObjectEntityFactory
{
    public function __construct(
        private readonly ReflectionClassToFileSetupEntity $reflectionClassToFileSetupEntity,
        private readonly PhpFileFactory $phpFileFactory,
    ) {
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public function create(ReflectionClass $class, ?FileSetupEntity $fileSetupEntity = null): ObjectEntity
    {
        $fileSetup = $this->reflectionClassToFileSetupEntity->transform($class, $fileSetupEntity);

        return new ObjectEntity($fileSetup, $class->getShortName() . 'Assert', $this->phpFileFactory->create());
    }
}
