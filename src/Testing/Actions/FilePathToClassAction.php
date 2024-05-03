<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Actions;

use StrictPhp\StrictMock\Testing\Constants\StubConstants;
use StrictPhp\StrictMock\Testing\Contracts\ComposerPsr4ServiceContract;
use StrictPhp\StrictMock\Testing\Contracts\FilePathToClassActionContract;
use StrictPhp\StrictMock\Testing\Exceptions\DirectoryDoesNotExistsException;
use StrictPhp\StrictMock\Testing\Helpers\Php;
use StrictPhp\StrictMock\Testing\Helpers\Replace;
use StrictPhp\StrictMock\Testing\Services\ComposerPsr4Service;

final class FilePathToClassAction implements FilePathToClassActionContract
{
    public function __construct(
        private readonly ComposerPsr4ServiceContract $composerPsr4Action,
    ) {
    }

    public function execute(string $filepath): ?string
    {
        $dirs = $this->composerPsr4Action->tryAll($filepath);

        foreach ($dirs as $ns => $dir) {
            $relative = Replace::start($filepath, $dir);

            if ($relative !== $filepath) {
                if ($relative === '') {
                    return $ns;
                }

                $class = $ns . ltrim(
                    strtr(Replace::end($relative, '.php'), '/', StubConstants::NameSpaceSeparator),
                    StubConstants::NameSpaceSeparator,
                );
                return Php::existClassInterfaceEnum($class) ? $class : null;
            }
        }

        throw new DirectoryDoesNotExistsException($filepath . ', not found in composer by psr-4.');
    }
}
