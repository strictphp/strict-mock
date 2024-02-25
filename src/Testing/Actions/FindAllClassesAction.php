<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Actions;

use LaraStrict\StrictMock\Testing\Attributes\TestAssert;
use LaraStrict\StrictMock\Testing\Contracts\FindAllClassesActionContract;
use LaraStrict\StrictMock\Testing\Contracts\FinderFactoryContract;
use ReflectionClass;

final class FindAllClassesAction implements FindAllClassesActionContract
{
    public function __construct(
        private readonly FinderFactoryContract $finderFactory,
        private readonly FilePathToClassAction $filePathToClassAction,
    ) {
    }

    /**
     * @return array<class-string>
     */
    public function execute(): array
    {
        $classes = [];
        foreach ($this->finderFactory->create() as $file) {
            $interface = $this->filePathToClassAction->execute($file->getRealPath());
            require_once $file->getPathname();

            if (interface_exists($interface, false) === false) {
                continue;
            }

            $classReflection = new ReflectionClass($interface);
            $attributes = $classReflection->getAttributes(TestAssert::class);
            if ($attributes === []) {
                continue;
            }
            $classes[] = $interface;
        }

        return $classes;
    }
}
