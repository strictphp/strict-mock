<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature\Testing\Contracts;

use Closure;
use LaraStrict\StrictMock\Testing\Expectation\AbstractExpectation;

final class TestFrameworkServiceContractAssertEqualsExpectation extends AbstractExpectation
{
    /**
     * @param Closure(string,string,string,self):void|null $_hook
     */
    public function __construct(
        public readonly string $return,
        public readonly string $expected,
        public readonly string $actual,
        public readonly string $message,
        public readonly ?Closure $_hook = null,
    ) {
    }
}
