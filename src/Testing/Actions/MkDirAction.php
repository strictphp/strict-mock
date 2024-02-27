<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Actions;

final class MkDirAction
{
    public function execute(string $path): void
    {
        if (is_dir($path) === false) {
            mkdir($path, 0o755, true);
        }
    }
}
