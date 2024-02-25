<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Assert\Actions;

use LaraStrict\StrictMock\Testing\Assert\Entities\AssertFileStateEntity;
use LaraStrict\StrictMock\Testing\Entities\ObjectEntity;
use LaraStrict\StrictMock\Testing\Entities\PhpDocEntity;
use LaraStrict\StrictMock\Testing\Enums\PhpType;
use Nette\PhpGenerator\Factory;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

final class GenerateAssertMethodAction
{
    private const HookProperty = '_hook';


    public function execute(
        AssertFileStateEntity $assertFileState,
        ReflectionMethod $method,
        ObjectEntity $expectationObject,
        PhpDocEntity $phpDoc,
    ): void
    {
        $parameters = $method->getParameters();

        $assertMethod = (new Factory())->fromMethodReflection($method);
        $assertFileState->class->addMember($assertMethod);

        $assertMethod->addBody(sprintf(
            '$_expectation = $this->getExpectation(%s::class);',
            $expectationObject->shortClassName,
        ));

        $hookParameters = [];

        if ($parameters !== []) {
            $assertMethod->addBody('$_message = $this->getDebugMessage();');
            $assertMethod->addBody('');

            foreach ($parameters as $parameter) {
                $hookParameters[] = sprintf('$%s', $parameter->name);
                $assertMethod->addBody(sprintf(
                    'Assert::assertEquals($_expectation->%s, $%s, $_message);',
                    $parameter->name,
                    $parameter->name
                ));
            }
        }

        $hookParameters[] = '$_expectation';

        $assertMethod->addBody('');

        $assertMethod->addBody(sprintf('if (is_callable($_expectation->%s)) {', self::HookProperty));
        $assertMethod->addBody(sprintf(
            '    call_user_func($_expectation->%s, %s);',
            self::HookProperty,
            implode(', ', $hookParameters),
        ));
        $assertMethod->addBody('}');

        $returnType = $method->getReturnType();

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
                $assertMethod->addBody('return $_expectation->return;');
                break;
            case PhpType::Self:
            case PhpType::Static:
                $assertMethod->addBody('');
                $assertMethod->addBody('return $this;');
                break;
        }
    }
}
