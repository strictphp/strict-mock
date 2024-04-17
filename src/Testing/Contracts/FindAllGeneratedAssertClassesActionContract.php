<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Contracts;

use Generator;

interface FindAllGeneratedAssertClassesActionContract
{
    /**
     * @return Generator<class-string>
     */
    public function execute(?string $dir = null): Generator;
}
