<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Contracts;


interface ComposerJsonServiceContract
{
    public function content(string $path): mixed;

    public function isExist(string $basePath): bool;
}
