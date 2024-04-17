<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Unit\Testing\Services;

use Closure;
use Exception;
use StrictPhp\StrictMock\Testing\Exceptions\FileDoesNotExistsException;
use StrictPhp\StrictMock\Testing\Helpers\Json;
use StrictPhp\StrictMock\Testing\Services\ComposerJsonService;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ComposerJsonServiceTest extends TestCase
{
    /**
     * @return array<string|int, array{0: Closure(static):void}>
     */
    public static function dataIsExist(): array
    {
        return [
            [static function (self $self) {
                $self->assertIsExist(__DIR__, false);
            }, ],
            [static function (self $self) {
                $self->assertIsExist(__DIR__ . '/../../../..', true);
            }, ],
        ];
    }

    /**
     * @param Closure(static):void $assert
     */
    #[DataProvider('dataIsExist')]
    public function testIsExist(Closure $assert): void
    {
        $assert($this);
    }

    public function assertIsExist(string $path, bool $expected): void
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
                static function (self $self) {
                    $self->assertContent(
                        __DIR__ . '/../../../..',
                        Json::loadFromFile(__DIR__ . '/../../../../composer.json'),
                    );
                },
            ],
            [
                static function (self $self) {
                    $self->assertContent(__DIR__, new FileDoesNotExistsException(__DIR__ . '/composer.json'));
                },
            ],
        ];
    }

    /**
     * @param Closure(static):void $assert
     */
    #[DataProvider('dataContent')]
    public function testContent(Closure $assert): void
    {
        $assert($this);
    }

    public function assertContent(string $path, array|Exception $expected): void
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
