<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Assert\Factories;

use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationCallsMap;
use LaraStrict\StrictMock\Testing\Assert\Entities\AssertFileStateEntity;
use LaraStrict\StrictMock\Testing\Entities\FileSetupEntity;
use Nette\PhpGenerator\Method;
use PHPUnit\Framework\Assert;
use ReflectionClass;

final class AssertFileStateEntityFactory
{
    public function __construct(
        private readonly AssertObjectEntityFactory $assertObjectEntityFactory
    )
    {
    }


    public function create(ReflectionClass $class, ?FileSetupEntity $fileSetupEntity = null): AssertFileStateEntity
    {
        $assertObject = $this->assertObjectEntityFactory->create($class, $fileSetupEntity);

        $assertNamespace = $assertObject->content->addNamespace($assertObject->exportSetup->namespace);
        $assertNamespace->addUse(Assert::class);
        $assertNamespace->addUse(AbstractExpectationCallsMap::class);
        $assertNamespace->addUse($class->getName());

        $classType = $assertNamespace->addClass($assertObject->shortClassName);
        $classType->addImplement($class->getName());

        $assertConstructor = new Method('__construct');
        $classType->setExtends(AbstractExpectationCallsMap::class);
        $classType->addMember($assertConstructor);
        $assertConstructor->addBody('parent::__construct();');

        return new AssertFileStateEntity($classType, $assertNamespace, $assertConstructor, $assertObject);
    }
}
