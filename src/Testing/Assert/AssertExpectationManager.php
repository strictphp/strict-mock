<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Assert;

use PHPUnit\Framework\Assert;
use Throwable;

final class AssertExpectationManager
{
    private static ?self $singleton = null;

    /**
     * @var array<AbstractExpectationCallsMap|AbstractExpectationAllInOne>
     */
    private array $currentExpectations = [];

    public static function getInstance(): self
    {
        if (! self::$singleton instanceof self) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    public static function resetSingleton(): void
    {
        self::$singleton = null;
    }

    public function assertCalled(): void
    {
        $errors = [];
        foreach ($this->currentExpectations as $map) {
            try {
                $map->assertCalled();
            } catch (Throwable $throwable) {
                $errors[] = $throwable;
            }
        }

        // We must some assert due the assertion count
        if ($errors === []) {
            Assert::assertEmpty($errors);

            return;
        }

        Assert::fail(implode(PHP_EOL, array_map(static fn ($e) => $e->getMessage(), $errors)));
    }

    public function hasExpectations(): bool
    {
        return $this->currentExpectations !== [];
    }

    public function register(AbstractExpectationCallsMap|AbstractExpectationAllInOne $map): void
    {
        $this->currentExpectations[] = $map;
    }

    public function reset(): void
    {
        $this->currentExpectations = [];
    }
}
