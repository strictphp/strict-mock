<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Assert\Actions;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PromotedParameter;
use ReflectionClass;
use ReflectionMethod;
use StrictPhp\StrictMock\Testing\Actions\WritePhpFileAction;
use StrictPhp\StrictMock\Testing\Assert\Entities\AssertFileStateEntity;
use StrictPhp\StrictMock\Testing\Assert\Factories\AssertFileStateEntityFactory;
use StrictPhp\StrictMock\Testing\Attributes\Expectation;
use StrictPhp\StrictMock\Testing\Attributes\IgnoreGenerateAssert;
use StrictPhp\StrictMock\Testing\Constants\StubConstants;
use StrictPhp\StrictMock\Testing\Entities\FileSetupEntity;
use StrictPhp\StrictMock\Testing\Entities\ObjectEntity;
use StrictPhp\StrictMock\Testing\Exceptions\IgnoreAssertException;
use StrictPhp\StrictMock\Testing\Expectation\Actions\ExpectationFileContentAction;
use StrictPhp\StrictMock\Testing\Expectation\Entities\ExpectationObjectEntity;
use StrictPhp\StrictMock\Testing\Factories\PhpDocEntityFactory;
use StrictPhp\StrictMock\Testing\Helpers\PhpGenerator;

final class GenerateAssertClassAction
{
    public function __construct(
        private readonly AssertFileStateEntityFactory $assertFileStateEntityFactory,
        private readonly PhpDocEntityFactory $parsePhpDocAction,
        private readonly ExpectationFileContentAction $expectationClassBuilderAction,
        private readonly WritePhpFileAction $writePhpFileAction,
        private readonly GenerateAssertMethodAction $generateAssertMethodAction,
    ) {
    }

    /**
     * @param ReflectionClass<object> $class
     *
     * @return array<ObjectEntity>
     *
     * @throws IgnoreAssertException
     */
    public function execute(ReflectionClass $class, ?FileSetupEntity $exportSetup = null): array
    {
        self::checkIgnoreAttribute($class);
        $assertFileState = $this->assertFileStateEntityFactory->create($class, $exportSetup);

        $expectationClasses = [];
        $generatedFiles = [$assertFileState->object];
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $phpDoc = $this->parsePhpDocAction->create($method);

            $expectation = $this->expectationClassBuilderAction->execute(
                classShortName: $class->getShortName(),
                phpNamespace: $assertFileState->phpNamespace,
                method: $method,
                phpDoc: $phpDoc,
            );

            $this->generateAssertMethodAction->execute(
                assertFileState: $assertFileState,
                method: $method,
                expectationClassShortName: (string) $expectation->classType->getName(),
                phpDoc: $phpDoc,
            );

            $staticMethodName = 'expectation' . ucfirst($method->getName());
            $staticMethod = $assertFileState->classType->addMethod($staticMethodName);
            $this->staticExpectationMethodBuilder($expectation, $staticMethod, $assertFileState->phpNamespace);

            $expectationClasses[$method->getName()] = $expectation->classType->getName();
        }
        $this->buildConstructorParameter($assertFileState, $expectationClasses);

        $this->writePhpFileAction->execute($assertFileState->object);

        return $generatedFiles;
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
    ): void {
        if ($expectationClasses === []) {
            return;
        }

        $constructor = $assertFileState->classType->getMethod(PhpGenerator::Constructor);
        $type = implode('|', $expectationClasses);

        $parameter = 'expectations';
        $body = sprintf('$this->setExpectations($%s);', $parameter);

        $constructor->addComment(sprintf('@param array<%s|null> $%s', $type, $parameter))
            ->addBody($body)
            ->addParameter($parameter)
            ->setType('array')
            ->setDefaultValue(new Literal('[]'));
    }

    private function staticExpectationMethodBuilder(
        ExpectationObjectEntity $expectation,
        Method $assertMethodStatic,
        PhpNamespace $phpNamespace,
    ): Method {
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

        $assertMethodStatic->setReturnType($phpNamespace->getName() . StubConstants::NameSpaceSeparator . $expectation->classType->getName())
            ->setStatic()
            ->setBody(
                sprintf('return new %s(%s);', $expectation->classType->getName(), implode(', ', $names))
            );

        return $assertMethodStatic;
    }
}
