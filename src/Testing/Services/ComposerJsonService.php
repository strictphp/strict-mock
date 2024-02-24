<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Services;

use LaraStrict\StrictMock\Testing\Exceptions\FileDoesNotExistsException;
use stdClass;

final class ComposerJsonService
{
    /**
     * @var array<string, stdClass>
     */
    private array $cache = [];


    public function isExist(string $basePath): bool
    {
        return is_file(self::composerJson($basePath));
    }


    public function content(string $path): mixed
    {
        $composer = self::composerJson($path);
        if (isset($this->cache[$composer])) {
            return $this->cache[$composer];
        }

        $content = file_get_contents($composer);
        if ($content === false) {
            throw new FileDoesNotExistsException($composer);
        }

        return $this->cache[$composer] = json_decode(
            $content,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }


    private static function composerJson(string $path): string
    {
        return $path . '/composer.json';
    }
}
