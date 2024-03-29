<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature\Testing\Contracts;

use Closure;
use LaraStrict\StrictMock\Testing\Expectation\AbstractExpectation;

final class TestFrameworkServiceContractAssertClassExpectation extends AbstractExpectation
{
    /**
     * @param Closure(self):void|null $_hook
     */
    public function __construct(
        public readonly string $return,
        public readonly ?Closure $_hook = null,
    ) {
    }
}
