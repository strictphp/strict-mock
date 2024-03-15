<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Factories;

use LaraStrict\StrictMock\Testing\Entities\FileSetupEntity;
use LaraStrict\StrictMock\Testing\Entities\ProjectSetupEntity;
use LaraStrict\StrictMock\Testing\Exceptions\LogicException;
use LaraStrict\StrictMock\Testing\Services\ComposerPsr4Service;

final class ProjectSetupEntityFactory
{
    public function __construct(
        private readonly string $composerDir,
        private readonly ComposerPsr4Service $composerJsonService,
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
        } elseif(isset($map[$this->export])) {
            $namespace = $this->export;
        } elseif(($key = array_search($this->export, $map, true)) !== false) {
            $namespace = $key;
        } else {
            throw new LogicException('$export does not contains in autoload-dev like namespace or path.');
        }

        return new ProjectSetupEntity($this->composerDir, new FileSetupEntity($map[$namespace], $namespace));
    }
}
