<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Contracts;

use Generator;

interface FindAllGeneratedAssertClassesActionContract
{
    /**
     * @return Generator<class-string>
     */
    public function execute(?string $dir = null): Generator;
}
