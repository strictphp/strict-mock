<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Assert\Actions;

use LaraStrict\StrictMock\Testing\Actions\WritePhpFileAction;
use LaraStrict\StrictMock\Testing\Exceptions\LogicException;
use LaraStrict\StrictMock\Testing\Expectation\Actions\ExpectationFileContentAction;
use LaraStrict\StrictMock\Testing\Factories\AssertFileStateEntityFactory;
use LaraStrict\StrictMock\Testing\Factories\PhpDocEntityFactory;
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
     * @return array<string, string>
     */
    public function execute(
        ReflectionClass $class,
    ): array
    {
        $assertFileState = $this->assertFileStateEntityFactory->create($class);
        // @todo check if exists and remove old

        $assertClassName = $assertFileState->class->getName();
        assert(is_string($assertClassName));

        $expectations = [];
        foreach (self::makeMethods($class) as $method) {
            $phpDoc = $this->parsePhpDocAction->create($method);

            $expectation = $this->expectationFileAction->execute(
                class: $class,
                fileSetup: $assertFileState->fileSetup,
                method: $method,
                phpDoc: $phpDoc,
            );
            $expectations[$expectation->folder] = $expectation->namespace;

            $this->generateAssertMethodAction->execute(
                assertClass: $assertFileState->class,
                method: $method,
                phpDoc: $phpDoc,
            );


            $assertFileState->expectationClasses[$method->getName()] = $expectation->namespace;
        }
        // $this->writePhpFileAction->execute();
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

}
