<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Dummy\Tests\Interfaces;

use Closure;
use StrictPhp\StrictMock\Testing\Expectation\AbstractExpectation;

final class BarFooExpectation extends AbstractExpectation
{
    /**
     * @param Closure(string,self):void|null $_hook
     */
    public function __construct(
        public readonly int $return,
        public readonly string $a,
        public readonly ?Closure $_hook = null,
    ) {
    }
}
