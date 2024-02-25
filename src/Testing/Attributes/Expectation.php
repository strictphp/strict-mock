<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Attributes;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
final class Expectation
{
    public function __construct(
        public readonly string $class,
    ) {
    }
}
