<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature\Testing\Helpers;

use Closure;
use LaraStrict\StrictMock\Testing\Exceptions\FileDoesNotExistsException;
use LaraStrict\StrictMock\Testing\Helpers\Realpath;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class RealpathTest extends TestCase
{

    /**
     * @return array<string|int, array{0: Closure(static):void}>
     */
    public static function data(): array
    {
        return [
            [
                function (self $self) {
                    $self->assert(
                        __DIR__ . '/../Assert',
                        realpath(__DIR__ . '/../Assert'),
                    );
                },
            ],
            [
                function (self $self) {
                    $self->assert(
                        __DIR__ . '/../Foo',
                        new FileDoesNotExistsException(__DIR__ . '/../Foo'),
                    );
                },
            ],
        ];
    }


    /**
     * @param Closure(static):void $assert
     * @dataProvider data
     */
    public function test(Closure $assert): void
    {
        $assert($this);
    }


    public function assert(
        string $path,
        string|FileDoesNotExistsException $expected
    ): void
    {
        if ($expected instanceof FileDoesNotExistsException) {
            $this->expectExceptionObject($expected);
        }

        Assert::assertSame($expected, Realpath::make($path));
    }

}
