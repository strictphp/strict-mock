<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Assert\Traits;

use LaraStrict\StrictMock\Testing\Assert\AssertExpectationManager;

trait AssertExpectationManagerTrait
{
    protected function assertPostConditions(): void
    {
        $manager = AssertExpectationManager::getInstance();

        if ($manager->hasExpectations()) {
            $this->addToAssertionCount(1);
            $manager->assertCalled();
        }

        parent::assertPostConditions();
    }

    protected function setUp(): void
    {
        parent::setUp();

        AssertExpectationManager::getInstance()->reset();
    }

    protected function tearDown(): void
    {
        AssertExpectationManager::getInstance()->reset();

        parent::tearDown();
    }
}
