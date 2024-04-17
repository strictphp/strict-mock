<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Testing\Contracts;

use Closure;
use StrictPhp\StrictMock\Testing\Expectation\AbstractExpectation;

final class ComposerJsonServiceContractContentExpectation extends AbstractExpectation
{
    /**
     * @param Closure(string,self):void|null $_hook
     */
    public function __construct(
        public readonly mixed $return,
        public readonly string $path,
        public readonly ?Closure $_hook = null,
    ) {
    }
}
