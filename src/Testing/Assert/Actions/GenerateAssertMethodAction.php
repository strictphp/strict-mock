<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Assert\Actions;

use LaraStrict\StrictMock\Testing\Actions\AddUseByTypeAction;
use LaraStrict\StrictMock\Testing\Assert\Entities\AssertFileStateEntity;
use LaraStrict\StrictMock\Testing\Contracts\TestFrameworkServiceContract;
use LaraStrict\StrictMock\Testing\Entities\ObjectEntity;
use LaraStrict\StrictMock\Testing\Entities\PhpDocEntity;
use LaraStrict\StrictMock\Testing\Enums\PhpType;
use Nette\PhpGenerator\Factory;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

final class GenerateAssertMethodAction
{
    private const ExpectationProperty = '_expectation';
    private const MessageProperty = '_message';
    private const HookProperty = '_hook';

    public function __construct(
        private readonly TestFrameworkServiceContract $testFrameworkService,
        private readonly AddUseByTypeAction $addUseByTypeAction,
    ) {
    }

    public function execute(
        AssertFileStateEntity $assertFileState,
        ReflectionMethod $method,
        ObjectEntity $expectationObject,
        PhpDocEntity $phpDoc,
    ): void {
        $parameters = $method->getParameters();

        $assertMethod = (new Factory())->fromMethodReflection($method);
        $assertFileState->class->addMember($assertMethod);

        $assertMethod->setPublic()
            ->addBody(sprintf(
                '$%s = $this->getExpectation(%s::class);',
                self::ExpectationProperty,
                $expectationObject->shortClassName,
            ));

        $hookParameters = [];

        if ($parameters !== []) {
            $assertFileState->namespace->addUse($this->testFrameworkService->assertClass());
            $assertMethod->addBody(sprintf('$%s = $this->getDebugMessage();', self::MessageProperty));
            $assertMethod->addBody('');

            foreach ($parameters as $parameter) {
                $this->addUseByTypeAction->execute($assertFileState->namespace, $parameter->getType());

                $hookParameters[] = sprintf('$%s', $parameter->name);
                $assertMethod->addBody(
                    $this->testFrameworkService->assertEquals(
                        sprintf('$%s->%s', self::ExpectationProperty, $parameter->name),
                        sprintf('$%s', $parameter->name),
                        '$' . self::MessageProperty
                    ) . ';',
                );
            }
        }

        $hookParameters[] = '$' . self::ExpectationProperty;

        $assertMethod->addBody('');

        $assertMethod->addBody(sprintf('if (is_callable($_expectation->%s)) {', self::HookProperty));
        $assertMethod->addBody(sprintf(
            '    ($%s->%s)(%s);',
            self::ExpectationProperty,
            self::HookProperty,
            implode(', ', $hookParameters),
        ));
        $assertMethod->addBody('}');

        $returnType = $method->getReturnType();

        $this->addUseByTypeAction->execute($assertFileState->namespace, $returnType);
        if ($returnType instanceof ReflectionNamedType) {
            $enumReturnType = PhpType::tryFrom($returnType->getName()) ?? PhpType::Mixed;
        } else if ($returnType instanceof ReflectionUnionType) {
            $enumReturnType = PhpType::Mixed;
        } else {
            $enumReturnType = $phpDoc->returnType;
        }

        switch ($enumReturnType) {
            case PhpType::Mixed:
                $assertMethod->addBody('');
                $assertMethod->addBody(sprintf('return $%s->return;', self::ExpectationProperty));
                break;
            case PhpType::Self:
            case PhpType::Static:
                $assertMethod->addBody('');
                $assertMethod->addBody('return $this;');
                break;
        }
    }

}
