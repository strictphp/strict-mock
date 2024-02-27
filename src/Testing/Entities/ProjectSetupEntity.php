<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Entities;

final class ProjectSetupEntity
{
    public function __construct(
        public readonly string $composerDir,
        public readonly FileSetupEntity $project,
        public readonly FileSetupEntity $export,
    ) {
    }
}
