<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Factories;

use StrictPhp\StrictMock\Testing\Constants\StubConstants;
use StrictPhp\StrictMock\Testing\Contracts\ComposerPsr4ServiceContract;
use StrictPhp\StrictMock\Testing\Entities\FileSetupEntity;
use StrictPhp\StrictMock\Testing\Entities\ProjectSetupEntity;
use StrictPhp\StrictMock\Testing\Exceptions\LogicException;
use StrictPhp\StrictMock\Testing\Helpers\Realpath;
use StrictPhp\StrictMock\Testing\Helpers\Replace;
use StrictPhp\StrictMock\Testing\Services\ComposerPsr4Service;

final class ProjectSetupEntityFactory
{
    public function __construct(
        private readonly string $composerDir,
        private readonly ComposerPsr4ServiceContract $composerJsonService,
        private readonly string $export = '',
    ) {
    }

    public function create(): ProjectSetupEntity
    {
        if ($this->composerJsonService->autoload($this->composerDir) === []) {
            throw new LogicException('The composer autoload psr-4 does not filled.');
        }

        $map = $this->composerJsonService->autoloadDev($this->composerDir);
        if (count($map) === 1) {
            $namespace = array_key_first($map);
        } elseif (isset($map[$this->export])) {
            $namespace = $this->export;
        } elseif (($key = array_search($this->export, $map, true)) !== false) {
            $namespace = $key;
        } else {
            throw new LogicException('$export does not contains in autoload-dev like namespace or path.');
        }

        if ($this->export !== '') {
            $path = Replace::start($this->export, $map[$namespace]);
            $suffixNs = Realpath::fromPath($path);
            $namespace .= $suffixNs;
            $map[$namespace] = $this->export;
        }

        return new ProjectSetupEntity($this->composerDir, new FileSetupEntity($map[$namespace], $namespace));
    }
}
