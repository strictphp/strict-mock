<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Contracts;

use SplFileInfo;

interface FinderFactoryContract
{
    /**
     * @return iterable<SplFileInfo>
     */
    public function create(string $path): iterable;
}
