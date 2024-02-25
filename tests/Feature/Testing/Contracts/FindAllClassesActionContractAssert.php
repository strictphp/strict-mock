<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature\Testing\Contracts;

use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationCallsMap;
use LaraStrict\StrictMock\Testing\Attributes\Expectation;
use LaraStrict\StrictMock\Testing\Contracts\FindAllClassesActionContract;
use PHPUnit\Framework\Assert;

#[Expectation(class: FindAllClassesActionContractExecuteExpectation::class)]
class FindAllClassesActionContractAssert extends AbstractExpectationCallsMap implements FindAllClassesActionContract
{
    /**
     * @param array<FindAllClassesActionContractExecuteExpectation|null> $execute
     */
    function __construct(array $execute = [])
    {
        parent::__construct();
        $this->setExpectations(FindAllClassesActionContractExecuteExpectation::class, $execute);
    }

    /**
     * @return array<class-string>
     */
    function execute(): array
    {
        $_expectation = $this->getExpectation(FindAllClassesActionContractExecuteExpectation::class);

        if (is_callable($_expectation->_hook)) {
            call_user_func($_expectation->_hook, $_expectation);
        }

        return $_expectation->return;
    }
}
