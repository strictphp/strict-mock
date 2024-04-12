<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Transformers;

use LaraStrict\StrictMock\Testing\Actions\MkDirAction;
use LaraStrict\StrictMock\Testing\Constants\StubConstants;
use LaraStrict\StrictMock\Testing\Entities\FileSetupEntity;
use LaraStrict\StrictMock\Testing\Entities\ProjectSetupEntity;
use LaraStrict\StrictMock\Testing\Helpers\Realpath;
use LaraStrict\StrictMock\Testing\Services\ComposerPsr4Service;
use ReflectionClass;

final class ReflectionClassToFileSetupEntity
{
    private const Vendor = 'Vendor';

    public function __construct(
        private readonly ProjectSetupEntity $projectSetup,
        private readonly MkDirAction $mkDirAction,
        private readonly ComposerPsr4Service $composerPsr4Service,
    ) {
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public function transform(ReflectionClass $class, ?FileSetupEntity $exportSetup = null): FileSetupEntity
    {
        $exportSetup ??= $this->projectSetup->export;
        $namespace = $class->getNamespaceName();
        $map = $this->composerPsr4Service->autoload($this->projectSetup->composerDir);

        foreach ($map as $ns => $root) {
            if (str_starts_with($namespace, $ns) === false) {
                continue;
            }
            $exportNamespace = str_replace($ns, $exportSetup->namespace, $namespace);
            $path = $class->getFileName();
            assert(is_string($path));
            $exportDir = str_replace($root, $exportSetup->dir, dirname($path));
            return new FileSetupEntity($exportDir, $exportNamespace);
        }

        $exportNamespace = $exportSetup->namespace . self::Vendor . StubConstants::NameSpaceSeparator . $namespace;
        $exportDir = implode(
            DIRECTORY_SEPARATOR,
            [$exportSetup->dir, self::Vendor, Realpath::fromNamespace($namespace)]
        );
        $this->mkDirAction->execute($exportDir);

        return new FileSetupEntity($exportDir, $exportNamespace);
    }
}
