<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Unit\Testing\Services;

use Closure;
use Exception;
use LaraStrict\StrictMock\Testing\Exceptions\FileDoesNotExistsException;
use LaraStrict\StrictMock\Testing\Services\ComposerJsonService;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class ComposerJsonServiceTest extends TestCase
{

    /**
     * @return array<string|int, array{0: Closure(static):void}>
     */
    public static function dataIsExist(): array
    {
        return [
            [
                function (self $self) {
                    $self->assertIsExist(
                        __DIR__,
                        false,
                    );
                },
            ],
            [
                function (self $self) {
                    $self->assertIsExist(
                        __DIR__ . '/../../../..',
                        true,
                    );
                },
            ],
        ];
    }


    /**
     * @param Closure(static):void $assert
     * @dataProvider dataIsExist
     */
    public function testIsExist(Closure $assert): void
    {
        $assert($this);
    }


    public function assertIsExist(
        string $path,
        bool $expected,
    ): void
    {
        Assert::assertSame($expected, (new ComposerJsonService())->isExist($path));
    }


    /**
     * @return array<string|int, array{0: Closure(static):void}>
     */
    public static function dataContent(): array
    {
        return [
            [
                function (self $self) {
                    $self->assertContent(
                        __DIR__ . '/../../../..',
                        json_decode(file_get_contents(__DIR__ . '/../../../../composer.json'), true),
                    );
                },
            ],
            [
                function (self $self) {
                    $self->assertContent(
                        __DIR__,
                        new FileDoesNotExistsException(__DIR__ . '/composer.json')
                    );
                },
            ],
        ];
    }


    /**
     * @param Closure(static):void $assert
     * @dataProvider dataContent
     */
    public function testContent(Closure $assert): void
    {
        $assert($this);
    }


    public function assertContent(
        string $path,
        array|Exception $expected,
    ): void
    {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }
        $composerJsonService = new ComposerJsonService();
        $actual = $composerJsonService->content($path);
        Assert::assertSame($expected, $actual);
        Assert::assertSame($actual, $composerJsonService->content($path));
    }

}
