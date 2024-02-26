<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Actions;

use Generator;
use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationCallsMap;
use LaraStrict\StrictMock\Testing\Contracts\FindAllGeneratedAssertClassesActionContract;
use LaraStrict\StrictMock\Testing\Contracts\FinderFactoryContract;
use LaraStrict\StrictMock\Testing\Entities\ProjectSetupEntity;
use LaraStrict\StrictMock\Testing\Exceptions\LogicException;
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
    public function execute(): Generator
    {
        foreach ($this->finderFactory->create($this->projectSetupEntity->exportRoot->folder) as $file) {
            $class = $this->filePathToClassAction->execute($file->getRealPath());

            $classReflection = new ReflectionClass($class);
            $parentClass = $classReflection->getParentClass();
            if ($parentClass === false || ($parentClass->getName() !== AbstractExpectationAllInOne::class && $parentClass->getName() !== AbstractExpectationCallsMap::class)) {
                continue;
            }

            $interfaces = $classReflection->getInterfaces();
            if (count($interfaces) !== 1) {
                throw new LogicException('Too many or too few implementations...');
            }

            yield reset($interfaces);
        }
    }
}
