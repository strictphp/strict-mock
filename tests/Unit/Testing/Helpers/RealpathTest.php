<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Unit\Testing\Helpers;

use Closure;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StrictPhp\StrictMock\Testing\Exceptions\FileDoesNotExistsException;
use StrictPhp\StrictMock\Testing\Helpers\Realpath;

final class RealpathTest extends TestCase
{
    /**
     * @return array<string|int, array{0: Closure(static):void}>
     */
    public static function data(): array
    {
        return [
            [static function (self $self) {
                $path = __DIR__ . '/../../Testing';
                $self->assert($path, realpath($path));
            }],
            [static function (self $self) {
                $self->assert(__DIR__ . '/../Foo', new FileDoesNotExistsException(__DIR__ . '/../Foo'));
            }],
        ];
    }

    /**
     * @param Closure(static):void $assert
     */
    #[DataProvider('data')]
    public function test(Closure $assert): void
    {
        $assert($this);
    }

    public function assert(string $path, string|FileDoesNotExistsException $expected): void
    {
        if ($expected instanceof FileDoesNotExistsException) {
            $this->expectExceptionObject($expected);
        }

        Assert::assertSame($expected, Realpath::make($path));
    }
}
