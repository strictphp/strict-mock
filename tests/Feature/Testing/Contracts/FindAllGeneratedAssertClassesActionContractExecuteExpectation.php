<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature\Testing\Contracts;

use Closure;
use Generator;
use LaraStrict\StrictMock\Testing\Expectation\AbstractExpectation;

final class FindAllGeneratedAssertClassesActionContractExecuteExpectation extends AbstractExpectation
{
    /**
     * @param Closure(self):void|null $_hook
     */
    public function __construct(
        public readonly Generator $return,
        public readonly ?Closure $_hook = null,
    ) {
    }
}
