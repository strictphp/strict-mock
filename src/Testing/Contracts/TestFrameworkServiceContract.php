<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Contracts;

use Nette\PhpGenerator\PhpNamespace;

interface TestFrameworkServiceContract
{

    public function assertEquals(PhpNamespace $namespace, string $expected, string $actual, string $message): string;
}
