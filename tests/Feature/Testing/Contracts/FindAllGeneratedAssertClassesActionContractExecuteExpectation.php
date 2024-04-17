<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Testing\Contracts;

use Closure;
use Generator;
use StrictPhp\StrictMock\Testing\Expectation\AbstractExpectation;

final class FindAllGeneratedAssertClassesActionContractExecuteExpectation extends AbstractExpectation
{
    /**
     * @param Closure(string|null,self):void|null $_hook
     */
    public function __construct(
        public readonly Generator $return,
        public readonly ?string $dir = null,
        public readonly ?Closure $_hook = null,
    ) {
    }
}
