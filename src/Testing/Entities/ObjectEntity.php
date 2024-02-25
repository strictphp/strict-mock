<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Entities;

use LaraStrict\StrictMock\Testing\Constants\StubConstants;
use Nette\PhpGenerator\PhpFile;

final class ObjectEntity
{
    /**
     * @var class-string<object>
     */
    public readonly string $class;

    public readonly string $pathname;

    public function __construct(
        public readonly FileSetupEntity $exportSetup,
        public readonly string $shortClassName,
        public readonly PhpFile $content,
    ) {
        /** @var class-string $class */
        $class = $this->exportSetup->namespace . StubConstants::NameSpaceSeparator . $this->shortClassName;
        $this->class = $class;
        $this->pathname = $this->exportSetup->folder . DIRECTORY_SEPARATOR . $this->shortClassName . '.php';
    }
}
