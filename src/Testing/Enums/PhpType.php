<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Enums;

enum PhpType: string
{
    case Self = 'self';
    case Static = 'static';
    case Mixed = 'mixed';
    case Unknown = 'unknown';
    case Void = 'void';
}
