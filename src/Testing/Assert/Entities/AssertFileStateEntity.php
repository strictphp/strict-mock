<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Assert\Entities;

use StrictPhp\StrictMock\Testing\Entities\ObjectEntity;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

class AssertFileStateEntity
{
    public function __construct(
        public readonly ClassType $class,
        public readonly PhpNamespace $namespace,
        public readonly ObjectEntity $object,
        public readonly bool $oneParameterOneExpectation,
    ) {
    }
}
