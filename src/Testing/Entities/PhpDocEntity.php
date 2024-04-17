<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Entities;

use StrictPhp\StrictMock\Testing\Enums\PhpType;

class PhpDocEntity
{
    public function __construct(
        public readonly PhpType $returnType = PhpType::Unknown,
    ) {
    }
}
