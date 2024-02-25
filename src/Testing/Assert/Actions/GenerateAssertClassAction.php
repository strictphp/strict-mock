<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Assert\Actions;

use LaraStrict\StrictMock\Testing\Actions\WritePhpFileAction;
use LaraStrict\StrictMock\Testing\Assert\Entities\AssertFileStateEntity;
use LaraStrict\StrictMock\Testing\Assert\Factories\AssertFileStateEntityFactory;
use LaraStrict\StrictMock\Testing\Attributes\Expectation;
use LaraStrict\StrictMock\Testing\Entities\FileSetupEntity;
use LaraStrict\StrictMock\Testing\Entities\ObjectEntity;
use LaraStrict\StrictMock\Testing\Exceptions\LogicException;
use LaraStrict\StrictMock\Testing\Expectation\Actions\ExpectationFileContentAction;
use LaraStrict\StrictMock\Testing\Factories\PhpDocEntityFactory;
use Nette\PhpGenerator\Literal;
use ReflectionClass;
use ReflectionMethod;

final class GenerateAssertClassAction
{
    public function __construct(
        private readonly AssertFileStateEntityFactory $assertFileStateEntityFactory,
        private readonly PhpDocEntityFactory $parsePhpDocAction,
        private readonly ExpectationFileContentAction $expectationFileAction,
        private readonly WritePhpFileAction $writePhpFileAction,
        private readonly GenerateAssertMethodAction $generateAssertMethodAction,
    )
    {
    }


    /**
     * @param ReflectionClass<object> $class
     * @return array<ObjectEntity>
     */
    public function execute(
        ReflectionClass $class,
        ?FileSetupEntity $exportSetup = null,
    ): array
    {
        // @todo check if exists and remove old

        $assertFileState = $this->assertFileStateEntityFactory->create($class, $exportSetup);

        $generatedFiles = [$assertFileState->object];
        foreach (self::makeMethods($class) as $method) {
            $phpDoc = $this->parsePhpDocAction->create($method);

            $generatedFiles[] = $expectation = $this->expectationFileAction->execute(
                class: $class,
                assertFileState: $assertFileState,
                method: $method,
                phpDoc: $phpDoc,
            );

            $this->generateAssertMethodAction->execute(
                assertFileState: $assertFileState,
                method: $method,
                expectationObject: $expectation,
                phpDoc: $phpDoc,
            );

            $assertFileState->expectationClasses[$method->getName()] = $expectation->shortClassName;
        }
        $this->addAttributes($assertFileState);

        $this->writePhpFileAction->execute($assertFileState->object);

        return $generatedFiles;
    }


    /**
     * @param ReflectionClass<object> $class
     * @return array<ReflectionMethod>
     */
    private static function makeMethods(ReflectionClass $class): array
    {
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        if ($methods === []) {
            throw new LogicException('Class %s does not contain any public', $class->getName());
            //        } elseif ($class->isInterface() === false) {
            //            throw new LogicException('Class %s is not interface', $class->getName());
        }

        return $methods;
    }


    private function addAttributes(AssertFileStateEntity $assertFileState): void
    {
        if ($assertFileState->expectationClasses === []) {
            return;
        }

        $assertFileState->namespace->addUse(Expectation::class);

        foreach ($assertFileState->expectationClasses as $methodName => $expectationClass) {
            $assertFileState->constructor->addComment(sprintf(
                '@param array<%s|null> $%s',
                $expectationClass,
                $methodName,
            ));

            $assertFileState->constructor->addBody(sprintf(
                '$this->setExpectations(%s::class, $%s);',
                $expectationClass,
                $methodName,
            ));

            $assertFileState->constructor
                ->addParameter($methodName)
                ->setType('array')
                ->setDefaultValue(new Literal('[]'));

            $assertFileState->class->addAttribute(Expectation::class, [
                'class' => new Literal($expectationClass . '::class'),
            ]);
        }
    }

}
