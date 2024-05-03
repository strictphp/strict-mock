<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Assert\Factories;

use ReflectionClass;
use StrictPhp\StrictMock\Testing\Entities\FileSetupEntity;
use StrictPhp\StrictMock\Testing\Entities\ObjectEntity;
use StrictPhp\StrictMock\Testing\Factories\PhpFileFactory;
use StrictPhp\StrictMock\Testing\Transformers\ReflectionClassToFileSetupEntity;

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
