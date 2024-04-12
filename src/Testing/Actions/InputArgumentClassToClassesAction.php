<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Actions;

use Generator;
use LaraStrict\StrictMock\Testing\Contracts\FindAllGeneratedAssertClassesActionContract;
use LaraStrict\StrictMock\Testing\Entities\ProjectSetupEntity;
use LaraStrict\StrictMock\Testing\Factories\ReflectionClassFactory;
use ReflectionClass;

final class InputArgumentClassToClassesAction
{
    public function __construct(
        private readonly FindAllGeneratedAssertClassesActionContract $findAllClassesAction,
        private readonly ReflectionClassFactory $reflectionClassFactory,
        private readonly ProjectSetupEntity $projectSetupEntity,
    ) {
    }

    /**
     * @param class-string|string|array<string>|array<class-string> $inputs
     *
     * @return Generator<ReflectionClass<object>>
     */
    public function execute(string|array $inputs): Generator
    {
        if ($inputs === 'all') {
            $classes = $this->findAllClassesAction->execute();
        } elseif (is_string($inputs)) {
            $dir = $this->projectSetupEntity->composerDir . DIRECTORY_SEPARATOR . ltrim($inputs, DIRECTORY_SEPARATOR);
            $classes = is_dir($dir) ? $this->findAllClassesAction->execute($dir) : [$inputs];
        } else {
            $classes = $inputs;
        }

        foreach ($classes as $class) {
            yield $class instanceof ReflectionClass ? $class : $this->reflectionClassFactory->create($class);
        }
    }
}
