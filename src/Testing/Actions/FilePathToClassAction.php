<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Actions;

use LaraStrict\StrictMock\Testing\Constants\StubConstants;
use LaraStrict\StrictMock\Testing\Exceptions\DirectoryDoesNotExistsException;
use LaraStrict\StrictMock\Testing\Helpers\Php;
use LaraStrict\StrictMock\Testing\Helpers\Replace;

final class FilePathToClassAction
{
    public function __construct(
        private readonly ComposerPsr4Action $composerPsr4Action,
    ) {
    }

    public function execute(string $filepath): ?string
    {
        $dirs = $this->composerPsr4Action->execute($filepath);

        foreach ($dirs as $ns => $dir) {
            $relative = Replace::start($filepath, $dir);

            if ($relative !== $filepath) {
                if ($relative === '') {
                    return $ns;
                }

                $class = $ns . ltrim(
                        strtr(
                            Replace::end($relative, '.php'),
                            '/',
                            StubConstants::NameSpaceSeparator,
                        ),
                        StubConstants::NameSpaceSeparator,
                    );
                return Php::existClassInterfaceEnum($class) ? $class : null;
            }
        }

        throw new DirectoryDoesNotExistsException($filepath . ', not found in composer by psr-4.');
    }

}
