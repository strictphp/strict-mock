<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Assert\Actions;

use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationCallsMap;
use LaraStrict\StrictMock\Testing\Attributes\Expectation;
use ReflectionClass;

final class RemoveAssertFileAction
{
    /**
     * @param ReflectionClass<AbstractExpectationCallsMap|AbstractExpectationAllInOne> $class
     *
     * @return array<string, class-string>
     */
    public function execute(ReflectionClass $class): array
    {
        $attributes = $class->getAttributes(Expectation::class);
        $assertFile = self::filePath($class);
        $removed[$assertFile] = $class->getName();

        foreach ($attributes as $attribute) {
            $expectation = $attribute->newInstance();
            assert($expectation instanceof Expectation);
            if (class_exists($expectation->class) === false) {
                continue;
            }

            $file = self::filePath($expectation->class);
            unlink($file);
            $removed[$file] = $expectation->class;
        }
        unlink($assertFile);

        return $removed;
    }

    /**
     * @param class-string|ReflectionClass $classOrReflection
     */
    private static function filePath(string|ReflectionClass $classOrReflection): string
    {
        $file = (is_string($classOrReflection)
            ? new ReflectionClass($classOrReflection)
            : $classOrReflection)
            ->getFileName();
        assert(is_string($file));

        return $file;
    }
}
