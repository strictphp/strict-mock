<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Actions;

use Illuminate\Contracts\Filesystem\Filesystem;
use Nette\PhpGenerator\PsrPrinter;
use StrictPhp\StrictMock\Testing\Entities\ObjectEntity;

final class WritePhpFileAction
{
    public function __construct(
        private readonly Filesystem $filesystem
    )
    {
    }

    public function execute(ObjectEntity $object): void
    {
        $this->filesystem->makeDirectory($object->exportSetup->dir);
        file_put_contents($object->pathname, (new PsrPrinter())->printFile($object->content));
    }
}
