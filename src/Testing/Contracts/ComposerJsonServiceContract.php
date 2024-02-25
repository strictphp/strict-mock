<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Contracts;

use LaraStrict\StrictMock\Testing\Attributes\OneParameterOneExpectation;

#[OneParameterOneExpectation]
interface ComposerJsonServiceContract
{
    public function content(string $path): mixed;

    public function isExist(string $basePath): bool;
}
