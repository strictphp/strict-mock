<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Factories;

use LaraStrict\StrictMock\Testing\Actions\FilePathToClassAction;
use LaraStrict\StrictMock\Testing\Entities\ProjectSetupEntity;
use LaraStrict\StrictMock\Testing\Exceptions\ClassIsNotInterfaceException;
use LaraStrict\StrictMock\Testing\Exceptions\FileDoesNotExistsException;
use ReflectionClass;

final class ReflectionClassFactory
{
    public function __construct(
        private readonly ProjectSetupEntity $projectSetup,
        private readonly FilePathToClassAction $filePathToClassAction,
    ) {
    }

    /**
     * @throws FileDoesNotExistsException
     * @throws ClassIsNotInterfaceException
     */
    public function create(string $classOrPath): ReflectionClass
    {
        if (class_exists($classOrPath) === false && interface_exists($classOrPath) === false) {
            $full = $this->projectSetup->projectRoot->folder . DIRECTORY_SEPARATOR . $classOrPath;
            if (is_file($full)) {
                $classOrPath = $full;
            } else if (is_file($classOrPath) === false) {
                throw new FileDoesNotExistsException($classOrPath);
            }
            $classOrPath = $this->filePathToClassAction->execute($classOrPath);
        }

        if (interface_exists($classOrPath) === false) {
            throw new ClassIsNotInterfaceException($classOrPath);
        }

        return new ReflectionClass($classOrPath);
    }
}
