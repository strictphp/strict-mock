<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Actions;

use Generator;
use LaraStrict\StrictMock\Testing\Contracts\FindAllGeneratedAssertClassesActionContract;
use LaraStrict\StrictMock\Testing\Factories\ReflectionClassFactory;
use ReflectionClass;

final class InputArgumentClassToClassesAction
{
    public function __construct(
        private readonly FindAllGeneratedAssertClassesActionContract $findAllClassesAction,
        private readonly ReflectionClassFactory $reflectionClassFactory,
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
        } else if (is_string($inputs)) {
            $classes = [$inputs];
        } else {
            $classes = $inputs;
        }

        foreach ($classes as $class) {
            yield $this->reflectionClassFactory->create($class);
        }
    }

}
