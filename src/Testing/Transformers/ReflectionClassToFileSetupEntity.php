<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Transformers;

use LaraStrict\StrictMock\Testing\Entities\FileSetupEntity;
use LaraStrict\StrictMock\Testing\Entities\ProjectSetupEntity;
use ReflectionClass;

final class ReflectionClassToFileSetupEntity
{
    public function __construct(private readonly ProjectSetupEntity $projectSetup)
    {
    }


    public function transform(ReflectionClass $class): FileSetupEntity
    {
        $namespace = $class->getNamespaceName();
        if (str_starts_with($namespace, $this->projectSetup->projectRoot->namespace)) {
            $exportNamespace = str_replace($this->projectSetup->projectRoot->namespace, $this->projectSetup->exportRoot->namespace, $namespace);
            $exportFile = str_replace($this->projectSetup->projectRoot->folder, $this->projectSetup->exportRoot->folder, $class->getFileName());
        } else {
            throw new \RuntimeException('not implemented');
        }

        return new FileSetupEntity(dirname($exportFile), $exportNamespace);
    }
}
