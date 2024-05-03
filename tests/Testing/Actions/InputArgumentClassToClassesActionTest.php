<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Testing\Actions;

use Closure;
use Generator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use StrictPhp\StrictMock\Testing\Actions\InputArgumentClassToClassesAction;
use StrictPhp\StrictMock\Testing\Entities\FileSetupEntity;
use StrictPhp\StrictMock\Testing\Entities\ProjectSetupEntity;
use Tests\StrictPhp\StrictMock\Feature\Dummy\App\Bazz;
use Tests\StrictPhp\StrictMock\Feature\Dummy\App\Contracts\Foo;
use Tests\StrictPhp\StrictMock\Feature\Testing\Contracts\FindAllGeneratedAssertClassesActionContractAssert;
use Tests\StrictPhp\StrictMock\Feature\Testing\Contracts\ReflectionClassFactoryContractAssert;

final class InputArgumentClassToClassesActionTest extends TestCase
{
    /**
     * @return array<string|int, array{0: Closure(static):void}>
     */
    public static function data(): array
    {
        return [
            'value of argument all' => [static function (self $self) {
                $baz = __DIR__ . '/../../Feature/Dummy/App/Bazz.php';
                $generator = (static function () use ($baz): Generator {
                    yield from [new ReflectionClass(Foo::class), Foo::class, $baz];
                })();

                $self->assert(
                    'all',
                    [
                        new ReflectionClass(Foo::class),
                        new ReflectionClass(Foo::class),
                        new ReflectionClass(Bazz::class),
                    ],
                    [FindAllGeneratedAssertClassesActionContractAssert::expectationExecute($generator)],
                    [
                        ReflectionClassFactoryContractAssert::expectationCreate(
                            new ReflectionClass(Foo::class),
                            Foo::class
                        ),
                        ReflectionClassFactoryContractAssert::expectationCreate(new ReflectionClass(Bazz::class), $baz),
                    ]
                );

            }],
            'file absolute' => [static function (self $self) {
                $fooRel = '/Feature/Dummy/App/Contracts/Foo.php';
                $fooFull = __DIR__ . '/../..' . $fooRel;
                $reflectionFoo = new ReflectionClass(Foo::class);

                $self->assert(
                    $fooFull,
                    [$reflectionFoo],
                    [],
                    [ReflectionClassFactoryContractAssert::expectationCreate($reflectionFoo, $fooFull)],
                );
            }],
            'class' => [static function (self $self) {
                $reflectionFoo = new ReflectionClass(Foo::class);

                $self->assert(
                    Foo::class,
                    [$reflectionFoo],
                    [],
                    [ReflectionClassFactoryContractAssert::expectationCreate($reflectionFoo, Foo::class)],
                );
            }],
            'file relative' => [static function (self $self) {
                $fooRel = '/Feature/Dummy/App/Contracts/Foo.php';
                $fooFull = __DIR__ . '/../..' . $fooRel;
                $reflectionFoo = new ReflectionClass(Foo::class);

                $self->assert(
                    $fooRel,
                    [$reflectionFoo],
                    [],
                    [ReflectionClassFactoryContractAssert::expectationCreate($reflectionFoo, $fooFull)],
                );
            }],
            'dir' => [static function (self $self) {
                $reflectionFoo = new ReflectionClass(Foo::class);
                $generator = (static function () use ($reflectionFoo): Generator {
                    yield from [$reflectionFoo];
                })();

                $fooRel = '/Feature/Dummy/App/Contracts/Foo.php';
                $fooFull = __DIR__ . '/../..' . $fooRel;

                $dirname = dirname($fooFull);

                $self->assert(
                    $dirname,
                    [$reflectionFoo],
                    [FindAllGeneratedAssertClassesActionContractAssert::expectationExecute($generator, $dirname)],
                    [ReflectionClassFactoryContractAssert::expectationCreate($reflectionFoo, $dirname)],
                );
            }],
            'mixed' => [static function (self $self) {
                $self->assert([
                    new ReflectionClass(Bazz::class),
                    __DIR__ . '/../../Feature/Dummy/App/Bazz.php',
                    Bazz::class,
                ], [
                    new ReflectionClass(Bazz::class),
                    new ReflectionClass(Bazz::class),
                    new ReflectionClass(Bazz::class),
                ], [], [
                    ReflectionClassFactoryContractAssert::expectationCreate(
                        new ReflectionClass(Bazz::class),
                        __DIR__ . '/../../Feature/Dummy/App/Bazz.php'
                    ),
                    ReflectionClassFactoryContractAssert::expectationCreate(
                        new ReflectionClass(Bazz::class),
                        Bazz::class
                    ),
                ]);
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
        mixed $input,
        array $expected,
        array $findAllGeneratedExpectations = [],
        array $reflectionClassFactoryExpectations = [],
    ): void {
        $action = new InputArgumentClassToClassesAction(
            new FindAllGeneratedAssertClassesActionContractAssert($findAllGeneratedExpectations),
            new ReflectionClassFactoryContractAssert($reflectionClassFactoryExpectations),
            new ProjectSetupEntity(__DIR__ . '/../..', new FileSetupEntity('', '')),
        );

        $classes = [];
        foreach ($action->execute($input) as $item) {
            $classes[] = $item;
        }

        Assert::assertEquals($expected, $classes);
    }
}
