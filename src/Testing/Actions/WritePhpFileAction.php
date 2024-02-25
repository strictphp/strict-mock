<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Actions;

use LaraStrict\StrictMock\Testing\Entities\ObjectEntity;
use Nette\PhpGenerator\PsrPrinter;

final class WritePhpFileAction
{

    public function execute(ObjectEntity $object): void
    {
        if (is_dir($object->exportSetup->folder) === false) {
            mkdir($object->exportSetup->folder, 0o755, true);
        }
        file_put_contents($object->pathname, (new PsrPrinter())->printFile($object->content));
    }
}
