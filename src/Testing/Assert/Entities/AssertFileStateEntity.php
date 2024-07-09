<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Assert\Entities;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use StrictPhp\StrictMock\Testing\Entities\ObjectEntity;

final class AssertFileStateEntity
{
    public function __construct(
        public readonly ClassType $classType,
        public readonly PhpNamespace $phpNamespace,
        public readonly ObjectEntity $object,
    ) {
    }
}
