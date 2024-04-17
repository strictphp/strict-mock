<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Services;

use StrictPhp\StrictMock\Testing\Contracts\ComposerJsonServiceContract;
use StrictPhp\StrictMock\Testing\Helpers\Json;

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
