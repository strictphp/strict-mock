<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Contracts;

use Generator;

interface ComposerPsr4ServiceContract
{
    /**
     * @return array<string, string>
     */
    public function autoload(string $realPath): array;

    /**
     * @return array<string, string>
     */
    public function autoloadDev(string $realPath): array;

    /**
     * @return Generator<string, string>
     */
    public function tryAll(string $realPath): Generator;
}
