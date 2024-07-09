<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Expectation\Entities;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;

final class ExpectationObjectEntity
{
    public function __construct(
        public readonly ClassType $classType,
        public readonly Method $constructor,
    ) {
    }
}
