<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Contracts;

use Illuminate\Console\Command;
use LaraStrict\StrictMock\Testing\Entities\FileSetupEntity;

interface GetNamespaceForStubsActionContract
{
    public function execute(Command $command, string $inputClass): FileSetupEntity;
}
