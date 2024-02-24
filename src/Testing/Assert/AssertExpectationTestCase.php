<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Assert;

use LaraStrict\StrictMock\Testing\Assert\Traits\AssertExpectationManagerTrait;
use PHPUnit\Framework\TestCase;

abstract class AssertExpectationTestCase extends TestCase
{
    use AssertExpectationManagerTrait;
}
