<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\PHPUnit\Services;

use LaraStrict\StrictMock\Testing\Contracts\TestFrameworkServiceContract;
use PHPUnit\Framework\Assert;

final class TestFrameworkService implements TestFrameworkServiceContract
{
    public function assertClass(): string
    {
        return Assert::class;
    }

    public function assertEquals(string $expected, string $actual, string $message): string
    {
        return sprintf('Assert::assertEquals(%s, %s, %s)', $expected, $actual, $message);
    }


}
