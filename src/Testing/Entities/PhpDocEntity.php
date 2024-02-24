<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Entities;

use LaraStrict\StrictMock\Testing\Enums\PhpType;

class PhpDocEntity
{
    public function __construct(
        public readonly PhpType $returnType = PhpType::Unknown,
    ) {
    }
}
