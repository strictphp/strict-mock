<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Helpers;

use LaraStrict\StrictMock\Testing\Exceptions\FileDoesNotExistsException;

final class Realpath
{
    public static function make(string $path): string
    {
        $realPath = realpath($path);
        if ($realPath === false) {
            throw new FileDoesNotExistsException($path);
        }

        return $realPath;
    }
}
