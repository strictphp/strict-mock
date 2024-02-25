<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Actions;

use LaraStrict\StrictMock\Testing\Constants\ComposerConstants;
use LaraStrict\StrictMock\Testing\Constants\StubConstants;
use LaraStrict\StrictMock\Testing\Contracts\ComposerJsonServiceContract;
use LaraStrict\StrictMock\Testing\Exceptions\DirectoryDoesNotExistsException;
use LaraStrict\StrictMock\Testing\Exceptions\FileDoesNotExistsException;
use LaraStrict\StrictMock\Testing\Helpers\Realpath;

final class FilePathToClassAction
{
    /**
     * @var array<string, array<string, string>>
     */
    private array $dirs = [];

    public function __construct(
        private readonly ComposerJsonServiceContract $composerJsonService,
    ) {
    }

    public function execute(string $filepath): string
    {
        $realPath = Realpath::make($filepath);
        $composerDir = $this->findComposer($realPath);
        $dirs = $this->prepareSourceDirs($composerDir);

        foreach ($dirs as $ns => $dir) {
            $relative = str_replace($dir, '', $realPath, $count);
            if ($count === 1) {
                $preClass = preg_replace(
                    '/\.php$/i',
                    '',
                    str_replace('/', StubConstants::NameSpaceSeparator, $relative),
                );
                assert($preClass !== null);

                return $ns . ltrim($preClass, StubConstants::NameSpaceSeparator);
            }
        }

        throw new DirectoryDoesNotExistsException($realPath . ', not found in composer by psr-4.');
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
     * @return array<string, string>
     */
    private function prepareSourceDirs(string $basePath): array
    {
        if (isset($this->dirs[$basePath])) {
            return $this->dirs[$basePath];
        }

        $data = $this->composerJsonService->content($basePath);
        $dirs = [];

        foreach ([ComposerConstants::AutoLoad, ComposerConstants::AutoLoadDev] as $section) {
            if (isset($data[$section][ComposerConstants::Psr4]) === false || is_array($data[$section][ComposerConstants::Psr4]) === false) {
                continue;
            }

            foreach ($data[$section][ComposerConstants::Psr4] as $ns => $path) {
                $dirs[$ns] = $basePath . DIRECTORY_SEPARATOR . trim((string) $path, '\\/');
            }
        }

        uasort($dirs, static fn (string $a, string $b) => strlen($b) <=> strlen($a));
        reset($dirs);

        return $this->dirs[$basePath] = $dirs;
    }
}
