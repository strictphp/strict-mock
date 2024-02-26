<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Contracts;

interface TestFrameworkServiceContract
{
    /**
     * @return class-string
     */
    public function assertClass(): string;

    public function assertEquals(string $expected, string $actual, string $message): string;
}
