<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Symfony\Factories;

use StrictPhp\StrictMock\Testing\Contracts\FinderFactoryContract;
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
