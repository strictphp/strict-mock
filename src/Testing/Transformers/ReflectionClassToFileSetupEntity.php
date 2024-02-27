<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Transformers;

use LaraStrict\StrictMock\Testing\Actions\MkDirAction;
use LaraStrict\StrictMock\Testing\Constants\StubConstants;
use LaraStrict\StrictMock\Testing\Entities\FileSetupEntity;
use LaraStrict\StrictMock\Testing\Entities\ProjectSetupEntity;
use LaraStrict\StrictMock\Testing\Helpers\Realpath;
use ReflectionClass;

final class ReflectionClassToFileSetupEntity
{
    private const Vendor = 'Vendor';

    public function __construct(
        private readonly ProjectSetupEntity $projectSetup,
        private readonly MkDirAction $mkDirAction,
    ) {
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public function transform(ReflectionClass $class, ?FileSetupEntity $exportSetup = null): FileSetupEntity
    {
        $exportSetup ??= $this->projectSetup->export;
        $projectRoot = $this->projectSetup->project;
        $namespace = $class->getNamespaceName();
        if (str_starts_with($namespace, $projectRoot->namespace)) {
            $exportNamespace = str_replace($projectRoot->namespace, $exportSetup->namespace, $namespace);
            $path = $class->getFileName();
            assert(is_string($path));
            $exportDir = str_replace($projectRoot->dir, $exportSetup->dir, dirname($path));
        } else {
            $exportNamespace = $exportSetup->namespace . self::Vendor . StubConstants::NameSpaceSeparator . $namespace;
            $exportDir = implode(DIRECTORY_SEPARATOR, [$exportSetup->dir, self::Vendor, Realpath::fromNamespace($namespace)]);
            $this->mkDirAction->execute($exportDir);
        }

        return new FileSetupEntity($exportDir, $exportNamespace);
    }
}
