<?php declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Contracts;

use ReflectionClass;

interface ReflectionClassFactoryContract
{
    /**
     * @return ReflectionClass<object>
     */
    public function create(string $classOrPath): ReflectionClass;
}
