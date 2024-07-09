<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Assert\Factories;

use Closure;
use ReflectionClass;
use StrictPhp\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use StrictPhp\StrictMock\Testing\Assert\Entities\AssertFileStateEntity;
use StrictPhp\StrictMock\Testing\Entities\FileSetupEntity;
use StrictPhp\StrictMock\Testing\Helpers\PhpGenerator;

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

        $phpNamespace = $assertObject->content->addNamespace($assertObject->exportSetup->namespace);
        $phpNamespace->addUse(Closure::class);

        $classType = $phpNamespace->addClass($assertObject->shortClassName);
        $classType->setFinal();
        $classType->addImplement($class->getName());
        $classType->setExtends(AbstractExpectationAllInOne::class);
        $classType->addMember(PhpGenerator::buildConstructor());

        return new AssertFileStateEntity($classType, $phpNamespace, $assertObject);
    }
}
