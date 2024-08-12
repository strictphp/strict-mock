<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Assert\Traits;

use StrictPhp\StrictMock\Testing\Assert\AssertExpectationManager;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\PostCondition;

trait AssertExpectationManagerTrait
{
    /**
     * @before
     */
    #[Before]
    protected function beforeStartAssertExpectationManager()
    {
        AssertExpectationManager::getInstance()->reset();
    }

    /**
     * @postCondition
     */
    #[PostCondition]
    protected function postConditionStartAssertExpectationManager(): void
    {
        $manager = AssertExpectationManager::getInstance();

        if ($manager->hasExpectations()) {
            $this->addToAssertionCount(1);
            $manager->assertCalled();
        }
    }
}
