<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Contracts;

interface FilePathToClassActionContract
{
    public function execute(string $filepath): ?string;
}
