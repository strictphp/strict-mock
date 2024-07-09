<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Actions;

use Nette\PhpGenerator\PhpNamespace;
use ReflectionClass;
use ReflectionType;

/**
 * @deprecated
 */
final class AddUseByTypeAction
{
    public function execute(PhpNamespace $namespace, ReflectionClass|ReflectionType|null $type): void
    {
    }
}
