<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Actions;

use Generator;
use ReflectionClass;
use StrictPhp\StrictMock\Testing\Contracts\FindAllGeneratedAssertClassesActionContract;
use StrictPhp\StrictMock\Testing\Contracts\ReflectionClassFactoryContract;
use StrictPhp\StrictMock\Testing\Entities\ProjectSetupEntity;
use StrictPhp\StrictMock\Testing\Helpers\Php;

final class InputArgumentClassToClassesAction
{
    public function __construct(
        private readonly FindAllGeneratedAssertClassesActionContract $findAllClassesAction,
        private readonly ReflectionClassFactoryContract $reflectionClassFactory,
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
            if (Php::existClassInterface($inputs)) {
                $classes = [$inputs];
            } else {
                if (file_exists($inputs) === false) {
                    $inputs = $this->projectSetupEntity->composerDir . DIRECTORY_SEPARATOR . ltrim($inputs, DIRECTORY_SEPARATOR);
                }

                $classes = is_dir($inputs) ? $this->findAllClassesAction->execute($inputs) : [$inputs];
            }
        } else {
            $classes = $inputs;
        }

        foreach ($classes as $class) {
            yield $class instanceof ReflectionClass ? $class : $this->reflectionClassFactory->create($class);
        }
    }
}
