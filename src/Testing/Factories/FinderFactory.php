<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Factories;

use LaraStrict\StrictMock\Testing\Actions\FilePathToClassAction;
use LaraStrict\StrictMock\Testing\Contracts\FinderFactoryContract;
use LaraStrict\StrictMock\Testing\Entities\ProjectSetupEntity;
use Symfony\Component\Finder\Finder;

final class FinderFactory implements FinderFactoryContract
{
    public function __construct(
        private readonly FilePathToClassAction $filePathToClassAction,
        private readonly ProjectSetupEntity $projectSetupEntity,
    ) {
    }

    public function create(): Finder
    {
        return Finder::create()->files()
            ->name('*.php')
            ->in($this->filePathToClassAction->execute($this->projectSetupEntity->projectRoot->folder))
            ->notName('*.blade.php');
    }
}
