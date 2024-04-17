<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Exceptions;

use RuntimeException;

final class LogicException extends RuntimeException
{
    /**
     * @param scalar ...$params
     */
    public function __construct(string $message, ...$params)
    {
        parent::__construct(sprintf($message, ...$params));
    }
}
