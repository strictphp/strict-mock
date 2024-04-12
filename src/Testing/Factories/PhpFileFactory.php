<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Factories;

use Nette\PhpGenerator\PhpFile;

final class PhpFileFactory
{
    public function create(): PhpFile
    {
        $file = new PhpFile();
        $file->setStrictTypes();

        return $file;
    }
}
