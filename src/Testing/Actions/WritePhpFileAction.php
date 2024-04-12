<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Actions;

use LaraStrict\StrictMock\Testing\Entities\ObjectEntity;
use Nette\PhpGenerator\PsrPrinter;

final class WritePhpFileAction
{
    public function __construct(
        private readonly MkDirAction $mkDirAction
    )
    {
    }

    public function execute(ObjectEntity $object): void
    {
        $this->mkDirAction->execute($object->exportSetup->dir);
        file_put_contents($object->pathname, (new PsrPrinter())->printFile($object->content));
    }
}
