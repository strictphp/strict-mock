<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Helpers;

use StrictPhp\StrictMock\Testing\Exceptions\FileDoesNotExistsException;

final class Json
{
    public static function loadFromFile(string $path): array
    {
        if (is_file($path) === false || ($content = file_get_contents($path)) === false) {
            throw new FileDoesNotExistsException($path);
        }

        return self::decode($content);
    }

    /**
     * @return array<mixed>
     */
    private static function decode(string $content): array
    {
        $json = json_decode(json: $content, associative: true, flags: JSON_THROW_ON_ERROR);
        assert(is_array($json));

        return $json;
    }
}
