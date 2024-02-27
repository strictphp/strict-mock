<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Assert\Actions;

use LaraStrict\StrictMock\Testing\Actions\WritePhpFileAction;
use LaraStrict\StrictMock\Testing\Assert\Entities\AssertFileStateEntity;
use LaraStrict\StrictMock\Testing\Assert\Factories\AssertFileStateEntityFactory;
use LaraStrict\StrictMock\Testing\Attributes\Expectation;
use LaraStrict\StrictMock\Testing\Attributes\IgnoreGenerateAssert;
use LaraStrict\StrictMock\Testing\Entities\FileSetupEntity;
use LaraStrict\StrictMock\Testing\Entities\ObjectEntity;
use LaraStrict\StrictMock\Testing\Exceptions\IgnoreAssertException;
use LaraStrict\StrictMock\Testing\Exceptions\LogicException;
use LaraStrict\StrictMock\Testing\Expectation\Actions\ExpectationFileContentAction;
use LaraStrict\StrictMock\Testing\Factories\PhpDocEntityFactory;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use ReflectionClass;
use ReflectionMethod;

final class GenerateAssertClassAction
{
    public function __construct(
        private readonly RemoveAssertFileAction $removeAssertFileAction,
        private readonly AssertFileStateEntityFactory $assertFileStateEntityFactory,
        private readonly PhpDocEntityFactory $parsePhpDocAction,
        private readonly ExpectationFileContentAction $expectationFileAction,
        private readonly WritePhpFileAction $writePhpFileAction,
        private readonly GenerateAssertMethodAction $generateAssertMethodAction,
    ) {
    }

    private static function buildAssertConstructor(): Method
    {
        return (new Method('__construct'))
            ->setPublic()
            ->addBody('parent::__construct();');
    }

    /**
     * @param ReflectionClass<object> $class
     *
     * @return array<ObjectEntity>
     * @throws IgnoreAssertException
     */
    public function execute(
        ReflectionClass $class,
        ?FileSetupEntity $exportSetup = null,
    ): array {
        self::checkIgnoreAttribute($class);

        $assertFileState = $this->assertFileStateEntityFactory->create($class, $exportSetup);
        if (class_exists($assertFileState->object->class)) {
            $reflectionAssert = new ReflectionClass($assertFileState->object->class);
            self::checkIgnoreAttribute($reflectionAssert);

            $this->removeAssertFileAction->execute($reflectionAssert);
        }

        $assertConstructor = self::buildAssertConstructor();
        $assertFileState->class->addMember($assertConstructor);

        $expectationClasses = [];
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

            $expectationClasses[$method->getName()] = $expectation->shortClassName;
        }
        $this->buildConstructorParameter($assertFileState, $expectationClasses, $assertConstructor);

        $this->writePhpFileAction->execute($assertFileState->object);

        return $generatedFiles;
    }

    /**
     * @throws IgnoreAssertException
     */
    private static function checkIgnoreAttribute(ReflectionClass $class): void
    {
        if ($class->getAttributes(IgnoreGenerateAssert::class) !== []) {
            throw new IgnoreAssertException($class->getName());
        }
    }

    /**
     * @param ReflectionClass<object> $class
     *
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

    /**
     * @param array<string, string> $expectationClasses
     */
    private function buildConstructorParameter(AssertFileStateEntity $assertFileState, array $expectationClasses, Method $assertConstructor): void
    {
        if ($expectationClasses === []) {
            return;
        }

        $assertFileState->namespace->addUse(Expectation::class);

        foreach ($expectationClasses as $methodName => $expectationClass) {
            if ($assertFileState->oneParameterOneExpectation) {
                $this->addConstructorParameter($assertConstructor, $expectationClass, $methodName);
            }

            $assertFileState->class->addAttribute(Expectation::class, [
                'class' => new Literal($expectationClass . '::class'),
            ]);
        }

        if ($assertFileState->oneParameterOneExpectation === false) {
            $this->addConstructorParameter($assertConstructor, implode('|', $expectationClasses));
        }
    }

    private function addConstructorParameter(Method $constructor, string $type, ?string $parameter = null): void
    {
        if ($parameter === null) {
            $parameter = 'expectations';
            $body = sprintf('$this->setExpectations($%s);', $parameter);
        } else {
            $body = sprintf('$this->setExpectations(%s::class, $%s);', $type, $parameter);
        }

        $constructor->addComment(sprintf(
            '@param array<%s|null> $%s',
            $type,
            $parameter,
        ));

        $constructor->addBody($body);

        $constructor
            ->addParameter($parameter)
            ->setType('array')
            ->setDefaultValue(new Literal('[]'));
    }

}
