<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\PHPUnit\Traits;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\PostCondition;
use StrictPhp\StrictMock\Testing\Assert\AssertExpectationManager;

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
