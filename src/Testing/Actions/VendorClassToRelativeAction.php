<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Actions;

use StrictPhp\StrictMock\Testing\Constants\StubConstants;
use StrictPhp\StrictMock\Testing\Exceptions\DirectoryDoesNotExistsException;
use StrictPhp\StrictMock\Testing\Helpers\Replace;
use StrictPhp\StrictMock\Testing\Services\ComposerPsr4Service;

final class VendorClassToRelativeAction
{
    public function __construct(
        private readonly ComposerPsr4Service $composerPsr4Action,
    ) {
    }

    public function execute(string $path): string
    {
        $dirs = $this->composerPsr4Action->tryAll($path);

        $realPath = dirname($path);
        foreach ($dirs as $ns => $dir) {
            if (str_starts_with($realPath, $dir)) {
                $relative = Replace::start($realPath, $dir);
                return DIRECTORY_SEPARATOR . trim(
                    strtr($ns, StubConstants::NameSpaceSeparator, DIRECTORY_SEPARATOR),
                    DIRECTORY_SEPARATOR
                ) . $relative;
            }
        }

        throw new DirectoryDoesNotExistsException($path);
    }
}
