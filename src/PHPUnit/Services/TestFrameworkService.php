<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\PHPUnit\Services;

use Nette\PhpGenerator\PhpNamespace;
use PHPUnit\Framework\Assert;
use StrictPhp\StrictMock\Testing\Contracts\TestFrameworkServiceContract;

final class TestFrameworkService implements TestFrameworkServiceContract
{
    public function assertEquals(PhpNamespace $namespace, string $expected, string $actual, string $message): string
    {
        $class = Assert::class;
        $rc = new \ReflectionClass($class);
        $namespace->addUse($class);
        return sprintf('%s::assertEquals(%s, %s, %s)', $rc->getShortName(), $expected, $actual, $message);
    }
}
