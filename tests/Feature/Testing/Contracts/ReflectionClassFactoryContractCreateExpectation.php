<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Testing\Contracts;

use Closure;
use StrictPhp\StrictMock\Testing\Expectation\AbstractExpectation;

final class ReflectionClassFactoryContractCreateExpectation extends AbstractExpectation
{
    /**
     * @param Closure(string,self):void|null $_hook
     */
    public function __construct(
        public readonly \ReflectionClass $return,
        public readonly string $classOrPath,
        public readonly ?Closure $_hook = null,
    ) {
    }
}
