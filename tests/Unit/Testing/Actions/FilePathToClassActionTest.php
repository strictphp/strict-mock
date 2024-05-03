<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Unit\Testing\Actions;

use Closure;
use Exception;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StrictPhp\StrictMock\Testing\Actions\FilePathToClassAction;
use StrictPhp\StrictMock\Testing\Contracts\ComposerJsonServiceContract;
use StrictPhp\StrictMock\Testing\Exceptions\FileDoesNotExistsException;
use StrictPhp\StrictMock\Testing\Helpers\Json;
use StrictPhp\StrictMock\Testing\Services\ComposerPsr4Service;
use Tests\StrictPhp\StrictMock\Feature\Testing\Contracts\ComposerJsonServiceContractAssert;
use Tests\StrictPhp\StrictMock\Feature\Testing\Contracts\ComposerJsonServiceContractContentExpectation;
use Tests\StrictPhp\StrictMock\Feature\Testing\Contracts\ComposerJsonServiceContractIsExistExpectation;

final class FilePathToClassActionTest extends TestCase
{
    /**
     * @return array<string|int, array{0: Closure(static):void}>
     */
    public static function data(): array
    {
        return [
            [static function (self $self) {
                $path = realpath(__DIR__ . '/../../../../src/Testing/Contracts/ComposerJsonServiceContract.php');
                $composer = __DIR__ . '/../../../../composer.json';
                $self->assert(
                    $path,
                    ComposerJsonServiceContract::class,
                    new ComposerJsonServiceContractContentExpectation(Json::loadFromFile($composer), dirname($path, 4)),
                    isExists: [
                        new ComposerJsonServiceContractIsExistExpectation(false, dirname($path)),
                        new ComposerJsonServiceContractIsExistExpectation(false, dirname($path, 2)),
                        new ComposerJsonServiceContractIsExistExpectation(false, dirname($path, 3)),
                        new ComposerJsonServiceContractIsExistExpectation(true, dirname($path, 4)),
                    ]
                );
            }],
            [static function (self $self) {
                $path = realpath(__DIR__ . '/../../../../src/Testing/Contracts/ComposerJsonServiceContract.php');

                $up = $path;
                $isExists = [];
                do {
                    $last = $up;
                    $up = dirname($last);
                    $isExists[] = new ComposerJsonServiceContractIsExistExpectation(false, $up);
                } while ($up !== $last);

                $self->assert(
                    $path,
                    expected: new FileDoesNotExistsException('composer.json'),
                    isExists: $isExists
                );
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

    public function assert(
        string $path,
        string|Exception $expected,
        ?ComposerJsonServiceContractContentExpectation $content = null,
        ?array $isExists = [],
    ): void {
        $action = (new FilePathToClassAction(
            composerPsr4Action: new ComposerPsr4Service(new ComposerJsonServiceContractAssert([$content], $isExists)),
        ));
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        $actual = $action->execute($path);

        Assert::assertSame($expected, $actual);
    }
}
