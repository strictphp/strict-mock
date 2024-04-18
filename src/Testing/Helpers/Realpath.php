<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Helpers;

use StrictPhp\StrictMock\Testing\Constants\StubConstants;
use StrictPhp\StrictMock\Testing\Exceptions\FileDoesNotExistsException;

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

    public static function fromPath(string $path): string
    {
        $val = trim(strtr($path, '/', StubConstants::NameSpaceSeparator), '\\/');
        if ($val !== '') {
            $val .= StubConstants::NameSpaceSeparator;
        }

        return $val;
    }
}
