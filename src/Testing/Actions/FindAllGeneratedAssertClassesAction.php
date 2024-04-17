<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Actions;

use Generator;
use StrictPhp\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use StrictPhp\StrictMock\Testing\Assert\AbstractExpectationCallsMap;
use StrictPhp\StrictMock\Testing\Contracts\FindAllGeneratedAssertClassesActionContract;
use StrictPhp\StrictMock\Testing\Contracts\FinderFactoryContract;
use StrictPhp\StrictMock\Testing\Entities\ProjectSetupEntity;
use StrictPhp\StrictMock\Testing\Exceptions\LogicException;
use ReflectionClass;

final class FindAllGeneratedAssertClassesAction implements FindAllGeneratedAssertClassesActionContract
{
    public function __construct(
        private readonly FinderFactoryContract $finderFactory,
        private readonly FilePathToClassAction $filePathToClassAction,
        private readonly ProjectSetupEntity $projectSetupEntity,
    ) {
    }

    /**
     * @return Generator<ReflectionClass>
     */
    public function execute(?string $dir = null): Generator
    {
        foreach ($this->finderFactory->create($dir ?? $this->projectSetupEntity->export->dir) as $file) {
            if ($file->isFile() === false) {
                continue;
            }
            $class = $this->filePathToClassAction->execute($file->getRealPath());
            if ($class === null) {
                continue;
            }

            $classReflection = new ReflectionClass($class);
            $parentClass = $classReflection->getParentClass();
            if ($parentClass === false || ($parentClass->getName() !== AbstractExpectationAllInOne::class && $parentClass->getName() !== AbstractExpectationCallsMap::class)) {
                continue;
            }

            $interfaces = $classReflection->getInterfaces();
            if ($interfaces === []) {
                throw new LogicException('Too few implementations for class "%s"', $class);
            }

            yield reset($interfaces);
        }
    }
}
