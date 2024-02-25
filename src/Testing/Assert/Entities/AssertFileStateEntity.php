<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Assert\Entities;

use LaraStrict\StrictMock\Testing\Entities\ObjectEntity;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;

class AssertFileStateEntity
{
    /**
     * @param array<string, string> $expectationClasses
     */
    public function __construct(
        public readonly ClassType $class,
        public readonly PhpNamespace $namespace,
        public readonly Method $constructor,
        public readonly ObjectEntity $object,
        public array $expectationClasses = [],
    )
    {
    }
}
