<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Assert\Factories;

use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationCallsMap;
use LaraStrict\StrictMock\Testing\Assert\Entities\AssertFileStateEntity;
use LaraStrict\StrictMock\Testing\Entities\FileSetupEntity;
use Nette\PhpGenerator\Method;
use ReflectionClass;

final class AssertFileStateEntityFactory
{
    public function __construct(
        private readonly AssertObjectEntityFactory $assertObjectEntityFactory,
    ) {
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public function create(ReflectionClass $class, ?FileSetupEntity $fileSetupEntity = null): AssertFileStateEntity
    {
        $assertObject = $this->assertObjectEntityFactory->create($class, $fileSetupEntity);
        $oneByOne = false;
        if (class_exists($assertObject->class)) {
            $parentClass = (new ReflectionClass($assertObject->class))->getParentClass();
            assert($parentClass instanceof ReflectionClass);
            $oneByOne = $parentClass->getName() === AbstractExpectationCallsMap::class;
        }

        $assertNamespace = $assertObject->content->addNamespace($assertObject->exportSetup->namespace);
        $assertNamespace->addUse($class->getName());

        $classType = $assertNamespace->addClass($assertObject->shortClassName);
        $classType->addImplement($class->getName());
        if ($oneByOne) {
            $assertNamespace->addUse(AbstractExpectationCallsMap::class);
            $classType->setExtends(AbstractExpectationCallsMap::class);
        } else {
            $assertNamespace->addUse(AbstractExpectationAllInOne::class);
            $classType->setExtends(AbstractExpectationAllInOne::class);
        }

        $assertConstructor = new Method('__construct');
        $assertConstructor
            ->setPublic()
            ->addBody('parent::__construct();');
        $classType->addMember($assertConstructor);

        return new AssertFileStateEntity($classType, $assertNamespace, $assertConstructor, $assertObject, $oneByOne);
    }
}
