<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Actions;

use LaraStrict\StrictMock\Testing\Constants\StubConstants;
use LaraStrict\StrictMock\Testing\Exceptions\DirectoryDoesNotExistsException;
use LaraStrict\StrictMock\Testing\Helpers\Replace;

final class VendorClassToRelativeAction
{
    public function __construct(
        private readonly ComposerPsr4Action $composerPsr4Action,
    ) {
    }

    public function execute(string $path): string
    {
        $dirs = $this->composerPsr4Action->execute($path);

        $realPath = dirname($path);
        foreach ($dirs as $ns => $dir) {
            if (str_starts_with($realPath, $dir)) {
                $relative = Replace::start($realPath, $dir);
                return DIRECTORY_SEPARATOR . trim(strtr($ns, StubConstants::NameSpaceSeparator, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR) . $relative;
            }
        }

        throw new DirectoryDoesNotExistsException($path);
    }

}
