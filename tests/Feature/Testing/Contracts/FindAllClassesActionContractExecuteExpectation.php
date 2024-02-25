<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature\Testing\Contracts;

use Closure;
use LaraStrict\StrictMock\Testing\Expectation\AbstractExpectation;

final class FindAllClassesActionContractExecuteExpectation extends AbstractExpectation
{
    /**
     * @param Closure(self):void|null $_hook
     */
    public function __construct(
        public readonly array $return,
        public readonly ?Closure $_hook = null,
    ) {
    }
}
