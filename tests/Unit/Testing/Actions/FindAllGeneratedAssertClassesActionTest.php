<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Unit\Testing\Actions;

use Closure;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use StrictPhp\StrictMock\Testing\Actions\FindAllGeneratedAssertClassesAction;
use StrictPhp\StrictMock\Testing\Entities\FileSetupEntity;
use StrictPhp\StrictMock\Testing\Entities\ProjectSetupEntity;
use StrictPhp\StrictMock\Testing\Exceptions\LogicException;
use Tests\StrictPhp\StrictMock\Feature\Dummy\App\Contracts\Foo;
use Tests\StrictPhp\StrictMock\Feature\Dummy\App\Interfaces\Bar;
use Tests\StrictPhp\StrictMock\Feature\Dummy\Tests\BrokenClass;
use Tests\StrictPhp\StrictMock\Feature\Dummy\Tests\Contracts\FooAssert;
use Tests\StrictPhp\StrictMock\Feature\Dummy\Tests\Interfaces\BarAssert;
use Tests\StrictPhp\StrictMock\Feature\Testing\Contracts\FilePathToClassActionContractAssert;
use Tests\StrictPhp\StrictMock\Feature\Testing\Contracts\FinderFactoryContractAssert;

final class FindAllGeneratedAssertClassesActionTest extends TestCase
{
    /**
     * @return array<string|int, array{0: Closure(static):void}>
     */
    public static function data(): array
    {
        return [
            [static function (self $self) {
                $self->assert(__DIR__ . '/BBB');
            }],
            [static function (self $self) {
                $self->assert(null);
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

    public function assert(?string $inputDir): void {
        $sourceDir = __DIR__ . '/AAA';
        $finder = [
            new SplFileInfo(__DIR__ . '/../../../Feature/Dummy/Tests/Contracts/FooAssert.php'),
            new SplFileInfo(__DIR__ . '/../../../Feature/Dummy/Tests/Contracts'), // dir
            new SplFileInfo(__DIR__ . '/../../../Feature/Dummy/Tests/Interfaces/BarAssert.php'),
            new SplFileInfo(__DIR__ . '/../../../Feature/Dummy/Tests/Interfaces/BarFooExpectation.php'),
        ];

        $action = new FindAllGeneratedAssertClassesAction(
            new FinderFactoryContractAssert([
                FinderFactoryContractAssert::expectationCreate($finder, $inputDir ?? $sourceDir),
            ]),
            new FilePathToClassActionContractAssert([
                FilePathToClassActionContractAssert::expectationExecute(
                    FooAssert::class,
                    realpath(__DIR__ . '/../../../Feature/Dummy/Tests/Contracts/FooAssert.php')
                ),
                FilePathToClassActionContractAssert::expectationExecute(
                    BarAssert::class,
                    realpath(__DIR__ . '/../../../Feature/Dummy/Tests/Interfaces/BarAssert.php')
                ),
                FilePathToClassActionContractAssert::expectationExecute(
                    null,
                    realpath(__DIR__ . '/../../../Feature/Dummy/Tests/Interfaces/BarFooExpectation.php')
                ),
            ]),
            new ProjectSetupEntity('', new FileSetupEntity($sourceDir, '')),
        );

        $class = [];
        foreach ($action->execute($inputDir) as $item) {
            $class[] = $item->getName();
        }

        $expected = [Foo::class, Bar::class];

        Assert::assertSame($expected, $class);
    }

    public function testBrokenClass(): void
    {
        $sourceDir = __DIR__ . '/AAA';
        $finder = [new SplFileInfo(__DIR__ . '/../../../Feature/Dummy/Tests/BrokenClass.php')];

        $action = new FindAllGeneratedAssertClassesAction(
            new FinderFactoryContractAssert([FinderFactoryContractAssert::expectationCreate($finder, $sourceDir)]),
            new FilePathToClassActionContractAssert([
                FilePathToClassActionContractAssert::expectationExecute(
                    BrokenClass::class,
                    realpath(__DIR__ . '/../../../Feature/Dummy/Tests/BrokenClass.php')
                ),
            ]),
            new ProjectSetupEntity('', new FileSetupEntity($sourceDir, '')),
        );

        $this->expectExceptionObject(new LogicException('Too few implementations for class "%s"', BrokenClass::class));
        foreach ($action->execute($sourceDir) as $item) {
            // intentionally empty
        }
    }
}
