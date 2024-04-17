<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Assert;

use StrictPhp\StrictMock\Testing\Assert\Traits\AssertExpectationManagerTrait;
use PHPUnit\Framework\TestCase;

abstract class AssertExpectationTestCase extends TestCase
{
    use AssertExpectationManagerTrait;
}
