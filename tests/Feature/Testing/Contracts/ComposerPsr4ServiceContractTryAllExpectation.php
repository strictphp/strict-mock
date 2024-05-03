<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Testing\Contracts;

use Closure;
use StrictPhp\StrictMock\Testing\Expectation\AbstractExpectation;

final class ComposerPsr4ServiceContractTryAllExpectation extends AbstractExpectation
{
    /**
     * @param Closure(string,self):void|null $_hook
     */
    public function __construct(
        public readonly \Generator $return,
        public readonly string $realPath,
        public readonly ?Closure $_hook = null,
    ) {
    }
}
