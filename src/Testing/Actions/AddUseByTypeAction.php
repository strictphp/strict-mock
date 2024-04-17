<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Actions;

use StrictPhp\StrictMock\Testing\Exceptions\LogicException;
use StrictPhp\StrictMock\Testing\Helpers\Php;
use Nette\PhpGenerator\PhpNamespace;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

final class AddUseByTypeAction
{
    public function execute(PhpNamespace $namespace, ReflectionClass|ReflectionType|null $type): void
    {
        return;

    }
}
