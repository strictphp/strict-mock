<?php declare(strict_types=1);

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

        if ($attributes === []) {
            return [];
        }

        $assertFile = $class->getFileName();
        assert(is_string($assertFile));
        $removed[$assertFile] = $class->getName();

        foreach ($attributes as $attribute) {
            $expectation = $attribute->newInstance();
            assert($expectation instanceof Expectation);
            if (class_exists($expectation->class) === false) {
                continue;
            }

            $file = (new ReflectionClass($expectation->class))->getFileName();
            assert(is_string($file));
            unlink($file);
            $removed[$file] = $expectation->class;
        }
        unlink($assertFile);

        return $removed;
    }
}
