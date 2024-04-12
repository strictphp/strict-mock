<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Services;

use Generator;
use LaraStrict\StrictMock\Testing\Constants\ComposerConstants;
use LaraStrict\StrictMock\Testing\Contracts\ComposerJsonServiceContract;
use LaraStrict\StrictMock\Testing\Exceptions\DirectoryDoesNotExistsException;
use LaraStrict\StrictMock\Testing\Exceptions\FileDoesNotExistsException;

final class ComposerPsr4Service
{
    /**
     * @var array<string, array<string, array<string, string>>>
     */
    private array $dirs = [];

    public function __construct(
        private readonly ComposerJsonServiceContract $composerJsonService,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function autoload(string $realPath): array
    {
        return $this->dependencies($realPath)[ComposerConstants::AutoLoad] ?? [];
    }

    /**
     * @return array<string, string>
     */
    public function autoloadDev(string $realPath): array
    {
        return $this->dependencies($realPath)[ComposerConstants::AutoLoadDev] ?? [];
    }

    /**
     * @return Generator<string, string>
     */
    public function tryAll(string $realPath): Generator
    {
        $map = $this->dependencies($realPath);
        if (isset($map[ComposerConstants::AutoLoad])) {
            foreach ($map[ComposerConstants::AutoLoad] as $ns => $path) {
                yield $ns => $path;
            }
        }

        if (isset($map[ComposerConstants::AutoLoadDev])) {
            foreach ($map[ComposerConstants::AutoLoadDev] as $ns => $path) {
                yield $ns => $path;
            }
        }
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function dependencies(string $realPath): array
    {
        $composerDir = $this->findComposer($realPath);

        return $this->prepareSourceDirs($composerDir);
    }

    private function findComposer(string $path): string
    {
        if (is_file($path)) {
            $path = dirname($path);
        }

        while ($this->composerJsonService->isExist($path) === false) {
            $up = dirname($path);
            if ($up === $path) {
                throw new FileDoesNotExistsException('composer.json');
            }
            $path = $up;
        }

        return $path;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function prepareSourceDirs(string $basePath): array
    {
        if (isset($this->dirs[$basePath])) {
            return $this->dirs[$basePath];
        }

        $data = $this->composerJsonService->content($basePath);
        $dirs = [];

        foreach ([ComposerConstants::AutoLoad, ComposerConstants::AutoLoadDev] as $section) {
            if (isset($data[$section][ComposerConstants::Psr4]) === false || is_array(
                $data[$section][ComposerConstants::Psr4]
            ) === false) {
                continue;
            }

            foreach ($data[$section][ComposerConstants::Psr4] as $ns => $path) {
                $dirs[$section][$ns] = $basePath . DIRECTORY_SEPARATOR . trim((string) $path, '\\/');
            }

            uasort($dirs[$section], static fn (string $a, string $b) => strlen($b) <=> strlen($a));
            reset($dirs[$section]);
        }

        if ($dirs === []) {
            throw new DirectoryDoesNotExistsException($basePath . ', not found in composer by psr-4.');
        }

        return $this->dirs[$basePath] = $dirs;
    }
}
