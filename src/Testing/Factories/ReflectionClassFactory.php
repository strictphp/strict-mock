<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Factories;

use StrictPhp\StrictMock\Testing\Actions\FilePathToClassAction;
use StrictPhp\StrictMock\Testing\Contracts\FilePathToClassActionContract;
use StrictPhp\StrictMock\Testing\Contracts\ReflectionClassFactoryContract;
use StrictPhp\StrictMock\Testing\Entities\ProjectSetupEntity;
use StrictPhp\StrictMock\Testing\Exceptions\ClassIsNotInterfaceException;
use StrictPhp\StrictMock\Testing\Exceptions\FileDoesNotExistsException;
use StrictPhp\StrictMock\Testing\Helpers\Php;
use ReflectionClass;

final class ReflectionClassFactory implements ReflectionClassFactoryContract
{
    public function __construct(
        private readonly ProjectSetupEntity $projectSetup,
        private readonly FilePathToClassActionContract $filePathToClassAction,
    ) {
    }

    /**
     * @return ReflectionClass<object>
     */
    public function create(string $classOrPath): ReflectionClass
    {
        if (Php::existClassInterface($classOrPath) === false) {
            $full = $this->projectSetup->composerDir . DIRECTORY_SEPARATOR . $classOrPath;
            if (is_file($full)) {
                $classOrPath = $full;
            } elseif (is_file($classOrPath) === false) {
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
