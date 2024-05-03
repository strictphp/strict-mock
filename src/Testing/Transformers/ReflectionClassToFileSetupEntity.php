<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Transformers;

use Illuminate\Contracts\Filesystem\Filesystem;
use ReflectionClass;
use StrictPhp\StrictMock\Testing\Constants\StubConstants;
use StrictPhp\StrictMock\Testing\Contracts\ComposerPsr4ServiceContract;
use StrictPhp\StrictMock\Testing\Entities\FileSetupEntity;
use StrictPhp\StrictMock\Testing\Entities\ProjectSetupEntity;
use StrictPhp\StrictMock\Testing\Helpers\Realpath;

final class ReflectionClassToFileSetupEntity
{
    private const Vendor = 'Vendor';

    public function __construct(
        private readonly ProjectSetupEntity $projectSetup,
        private readonly Filesystem $filesystem,
        private readonly ComposerPsr4ServiceContract $composerPsr4Service,
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
        $this->filesystem->makeDirectory($exportDir);

        return new FileSetupEntity($exportDir, $exportNamespace);
    }
}
