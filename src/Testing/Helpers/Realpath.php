<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Helpers;

use LaraStrict\StrictMock\Testing\Constants\StubConstants;
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

    public static function fromNamespace(string $namespace): string
    {
        return trim(strtr($namespace, StubConstants::NameSpaceSeparator, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
    }
}
