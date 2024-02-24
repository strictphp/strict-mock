<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Factories;

use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationCallsMap;
use LaraStrict\StrictMock\Testing\Entities\AssertFileStateEntity;
use LaraStrict\StrictMock\Testing\Transformers\ReflectionClassToFileSetupEntity;
use Nette\PhpGenerator\Method;
use PHPUnit\Framework\Assert;
use ReflectionClass;

final class AssertFileStateEntityFactory
{
    public function __construct(
        private readonly PhpFileFactory $phpFileFactory,
        private readonly ReflectionClassToFileSetupEntity $namespaceAction,
    )
    {
    }


    public function create(ReflectionClass $class): AssertFileStateEntity
    {
        $fileSetup = $this->namespaceAction->transform($class);
        $file = $this->phpFileFactory->create();

        $assertNamespace = $file->addNamespace($fileSetup->namespace);
        $assertNamespace->addUse(Assert::class);

        $className = $class->getShortName() . 'Assert';
        $classType = $assertNamespace->addClass($className);
        $classType->addImplement($class->getName());

        $assertConstructor = new Method('__construct');
        $classType->setExtends(AbstractExpectationCallsMap::class);
        $classType->addMember($assertConstructor);
        $assertConstructor->addBody('parent::__construct();');

        return new AssertFileStateEntity($file, $classType, $className, $assertConstructor, $fileSetup);
    }
}
