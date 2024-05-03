<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Assert\Factories;

use Closure;
use ReflectionClass;
use StrictPhp\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use StrictPhp\StrictMock\Testing\Assert\AbstractExpectationCallsMap;
use StrictPhp\StrictMock\Testing\Assert\Entities\AssertFileStateEntity;
use StrictPhp\StrictMock\Testing\Entities\FileSetupEntity;

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
        $classType->setFinal();
        $classType->addImplement($class->getName());
        if ($oneByOne) {
            $assertNamespace->addUse(AbstractExpectationCallsMap::class);
            $classType->setExtends(AbstractExpectationCallsMap::class);
        } else {
            $assertNamespace->addUse(AbstractExpectationAllInOne::class);
            $classType->setExtends(AbstractExpectationAllInOne::class);
        }
        $assertNamespace->addUse(Closure::class);

        return new AssertFileStateEntity($classType, $assertNamespace, $assertObject, $oneByOne);
    }
}
