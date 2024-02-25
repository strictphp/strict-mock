<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Unit\Testing\Actions;

use Closure;
use LaraStrict\StrictMock\Testing\Actions\FilePathToClassAction;
use PHPUnit\Framework\TestCase;

final class FilePathToClassActionTest extends TestCase
{
    /**
     * @return array<string|int, array{0: Closure(static):void}>
     */
    public function data(): array
    {
        return [
            [function (self $self) {
                $self->assert(

                );
            }],
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

    ): void {
        new FilePathToClassAction(

        );
    }
}
