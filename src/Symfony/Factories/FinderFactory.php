<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Symfony\Factories;

use LaraStrict\StrictMock\Testing\Contracts\FinderFactoryContract;
use Symfony\Component\Finder\Finder;

final class FinderFactory implements FinderFactoryContract
{
    public function create(string $path): Finder
    {
        return Finder::create()->files()
            ->name('*.php')
            ->in($path)
            ->notName('*.blade.php');
    }
}
