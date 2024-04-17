<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Assert\Actions;

use StrictPhp\StrictMock\Testing\Actions\WritePhpFileAction;
use StrictPhp\StrictMock\Testing\Assert\Entities\AssertFileStateEntity;
use StrictPhp\StrictMock\Testing\Assert\Factories\AssertFileStateEntityFactory;
use StrictPhp\StrictMock\Testing\Attributes\Expectation;
use StrictPhp\StrictMock\Testing\Attributes\IgnoreGenerateAssert;
use StrictPhp\StrictMock\Testing\Entities\FileSetupEntity;
use StrictPhp\StrictMock\Testing\Entities\ObjectEntity;
use StrictPhp\StrictMock\Testing\Exceptions\IgnoreAssertException;
use StrictPhp\StrictMock\Testing\Expectation\Actions\ExpectationFileContentAction;
use StrictPhp\StrictMock\Testing\Expectation\Entities\ExpectationFileEntity;
use StrictPhp\StrictMock\Testing\Factories\PhpDocEntityFactory;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PromotedParameter;
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

    /**
     * @param ReflectionClass<object> $class
     *
     * @return array<ObjectEntity>
     */
    public function execute(ReflectionClass $class, ?FileSetupEntity $exportSetup = null): array {
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
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $phpDoc = $this->parsePhpDocAction->create($method);

             $expectation = $this->expectationFileAction->execute(
                 class: $class,
                 assertFileState: $assertFileState,
                 method: $method,
                 phpDoc: $phpDoc,
             );

            $this->generateAssertMethodAction->execute(
                assertFileState: $assertFileState,
                method: $method,
                expectationObject: $expectation->object,
                phpDoc: $phpDoc,
            );

            $assertFileState->namespace->addUse($expectation->object->class);
            $staticMethodName = 'expectation' . ucfirst($method->getName());
            $staticMethod = $assertFileState->class->addMethod($staticMethodName);
            $this->staticExpectationMethodBuilder($expectation, $staticMethod);

            $expectationClasses[$method->getName()] = $expectation->object->shortClassName;

            $generatedFiles[] = $expectation->object;
        }
        $this->buildConstructorParameter($assertFileState, $expectationClasses, $assertConstructor);

        $this->writePhpFileAction->execute($assertFileState->object);

        return $generatedFiles;
    }

    private static function buildAssertConstructor(): Method
    {
        return (new Method('__construct'))
            ->setPublic()
            ->addBody('parent::__construct();');
    }

    private static function checkIgnoreAttribute(ReflectionClass $class): void
    {
        if ($class->getAttributes(IgnoreGenerateAssert::class) !== []) {
            throw new IgnoreAssertException($class->getName());
        }
    }

    /**
     * @param array<string, string> $expectationClasses
     */
    private function buildConstructorParameter(
        AssertFileStateEntity $assertFileState,
        array $expectationClasses,
        Method $assertConstructor
    ): void
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

        $constructor->addComment(sprintf('@param array<%s|null> $%s', $type, $parameter));

        $constructor->addBody($body);

        $constructor
            ->addParameter($parameter)
            ->setType('array')
            ->setDefaultValue(new Literal('[]'));
    }

    private function staticExpectationMethodBuilder(
        ExpectationFileEntity $expectation,
        Method $assertMethodStatic
    ): Method
    {
        $names = [];
        foreach ($expectation->constructor->getParameters() as $parameter) {
            assert($parameter instanceof PromotedParameter);
            $names[] = '$' . $parameter->getName();

            $param = $assertMethodStatic->addParameter($parameter->getName())
                ->setNullable($parameter->isNullable())
                ->setType($parameter->getType())
                ->setComment($parameter->getComment())
                ->setAttributes($parameter->getAttributes());

            if ($parameter->hasDefaultValue()) {
                $param->setDefaultValue($parameter->getDefaultValue());
            }
        }

        $assertMethodStatic->setReturnType($expectation->object->class);
        $assertMethodStatic->setStatic();
        $assertMethodStatic->setBody(
            sprintf('return new %s(%s);', $expectation->object->shortClassName, implode(', ', $names))
        );

        return $assertMethodStatic;
    }
}
