<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Contracts;

use Symfony\Component\Finder\Finder;

interface FinderFactoryContract
{
    public function create(): Finder;
}
