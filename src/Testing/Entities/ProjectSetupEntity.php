<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Entities;

final class ProjectSetupEntity
{
    public function __construct(
        public readonly FileSetupEntity $projectRoot,
        public readonly FileSetupEntity $exportRoot,
        public readonly bool $oneParameterOneExpectation = true,
    ) {
    }
}
