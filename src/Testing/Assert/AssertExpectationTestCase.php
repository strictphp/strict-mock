<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Assert;

use PHPUnit\Framework\TestCase;
use StrictPhp\StrictMock\Testing\Assert\Traits\AssertExpectationManagerTrait;

abstract class AssertExpectationTestCase extends TestCase
{
    use AssertExpectationManagerTrait;
}
