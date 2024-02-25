<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Unit\Testing\Actions;

use Closure;
use LaraStrict\StrictMock\Testing\Actions\FilePathToClassAction;
use LaraStrict\StrictMock\Testing\Contracts\ComposerJsonServiceContract;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Tests\LaraStrict\StrictMock\Feature\Json;
use Tests\LaraStrict\StrictMock\Feature\Testing\Contracts\ComposerJsonServiceContractAssert;
use Tests\LaraStrict\StrictMock\Feature\Testing\Contracts\ComposerJsonServiceContractContentExpectation;
use Tests\LaraStrict\StrictMock\Feature\Testing\Contracts\ComposerJsonServiceContractIsExistExpectation;

final class FilePathToClassActionTest extends TestCase
{
    /**
     * @return array<string|int, array{0: Closure(static):void}>
     */
    public static function data(): array
    {
        return [
            [function (self $self) {
                $path = realpath(__DIR__ . '/../../../../src/Testing/Contracts/ComposerJsonServiceContract.php');
                $composer = __DIR__ . '/../../../../composer.json';

                $self->assert(
                    $path,
                    ComposerJsonServiceContract::class,
                    new ComposerJsonServiceContractContentExpectation(Json::loadFromFile($composer), dirname($path, 4))
                );
            }],
        ];
    }


    /**
     * @param Closure(static):void $assert
     *
     * @dataProvider data
     */
    public function test(Closure $assert): void
    {
        $assert($this);
    }

    public function assert(
        string $path,
        string $expected,
        ?ComposerJsonServiceContractContentExpectation $content = null
    ): void {
        $actual = (new FilePathToClassAction(
            composerJsonService: new ComposerJsonServiceContractAssert(
                isExist: [
                    new ComposerJsonServiceContractIsExistExpectation(false, dirname($path)),
                    new ComposerJsonServiceContractIsExistExpectation(false, dirname($path, 2)),
                    new ComposerJsonServiceContractIsExistExpectation(false, dirname($path, 3)),
                    new ComposerJsonServiceContractIsExistExpectation(true, dirname($path, 4)),
                ],
                content: [$content],
            )
        ))->execute($path);

        Assert::assertSame($expected, $actual);
    }
}
