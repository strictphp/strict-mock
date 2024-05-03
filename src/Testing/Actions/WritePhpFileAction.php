<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Actions;

use Illuminate\Contracts\Filesystem\Filesystem;
use StrictPhp\StrictMock\Testing\Entities\ObjectEntity;
use Nette\PhpGenerator\PsrPrinter;

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
