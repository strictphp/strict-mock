<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Assert\Actions;

use Nette\PhpGenerator\Factory;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use StrictPhp\StrictMock\Testing\Actions\AddUseByTypeAction;
use StrictPhp\StrictMock\Testing\Assert\Entities\AssertFileStateEntity;
use StrictPhp\StrictMock\Testing\Contracts\TestFrameworkServiceContract;
use StrictPhp\StrictMock\Testing\Entities\ObjectEntity;
use StrictPhp\StrictMock\Testing\Entities\PhpDocEntity;
use StrictPhp\StrictMock\Testing\Enums\PhpType;

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
        $assertMethod->setComment(null);

        // fix nullable
        foreach ($parameters as $parameter) {
            $assertMethod->getParameter($parameter->getName())
                ->setNullable($parameter->allowsNull());
        }

        $assertFileState->class->addMember($assertMethod);

        $assertMethod->setPublic()
            ->addBody(sprintf(
                '$%s = $this->getExpectation(%s::class);',
                self::ExpectationProperty,
                $expectationObject->shortClassName,
            ));

        $hookParameters = [];

        if ($parameters !== []) {
            $assertMethod->addBody(sprintf('$%s = $this->getDebugMessage();', self::MessageProperty));
            $assertMethod->addBody('');

            foreach ($parameters as $parameter) {
                $this->addUseByTypeAction->execute($assertFileState->namespace, $parameter->getType());

                $hookParameters[] = sprintf('$%s', $parameter->getName());
                $assertMethod->addBody(
                    $this->testFrameworkService->assertEquals(
                        $assertFileState->namespace,
                        sprintf('$%s->%s', self::ExpectationProperty, $parameter->name),
                        sprintf('$%s', $parameter->name),
                        '$' . self::MessageProperty,
                    ) . ';',
                );
            }
        }

        $hookParameters[] = '$' . self::ExpectationProperty;

        $assertMethod->addBody('');

        $assertMethod->addBody(sprintf(
            '$_expectation->%s !== null && ($%s->%s)(%s);',
            self::HookProperty,
            self::ExpectationProperty,
            self::HookProperty,
            implode(', ', $hookParameters),
        ));

        $returnType = $method->getReturnType();

        $this->addUseByTypeAction->execute($assertFileState->namespace, $returnType);
        if ($returnType instanceof ReflectionNamedType) {
            $enumReturnType = PhpType::tryFrom($returnType->getName()) ?? PhpType::Mixed;
        } elseif ($returnType instanceof ReflectionUnionType) {
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
