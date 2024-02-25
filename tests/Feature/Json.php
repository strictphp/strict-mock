<?php declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature;

final class Json
{
    public static function loadFromFile(string $path): array
    {
        $content = file_get_contents($path);
        assert(is_string($content));

        $json = json_decode($content, true);
        assert(is_array($json));

        return $json;
    }
}
