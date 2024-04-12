<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Services;

use LaraStrict\StrictMock\Testing\Contracts\ComposerJsonServiceContract;
use LaraStrict\StrictMock\Testing\Helpers\Json;

final class ComposerJsonService implements ComposerJsonServiceContract
{
    /**
     * @var array<string, array<mixed>>
     */
    private array $cache = [];

    public function content(string $path): mixed
    {
        $composer = self::composerJson($path);

        return $this->cache[$composer] ?? ($this->cache[$composer] = Json::loadFromFile($composer));
    }

    public function isExist(string $basePath): bool
    {
        return is_file(self::composerJson($basePath));
    }

    private static function composerJson(string $path): string
    {
        return $path . '/composer.json';
    }
}
