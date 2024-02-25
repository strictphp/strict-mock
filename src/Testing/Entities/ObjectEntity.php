<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Entities;

use LaraStrict\StrictMock\Testing\Constants\StubConstants;
use Nette\PhpGenerator\PhpFile;

final class ObjectEntity
{
    public readonly string $class;

    public readonly string $pathname;


    public function __construct(
        public readonly FileSetupEntity $exportSetup,
        public readonly string $shortClassName,
        public readonly PhpFile $content,
    )
    {
        $this->class = $this->exportSetup->namespace . StubConstants::NameSpaceSeparator . $this->shortClassName;
        $this->pathname = $this->exportSetup->folder . DIRECTORY_SEPARATOR . $this->shortClassName . '.php';
    }
}
