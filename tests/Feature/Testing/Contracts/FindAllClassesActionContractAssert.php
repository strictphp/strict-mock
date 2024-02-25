<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature\Testing\Contracts;

use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use LaraStrict\StrictMock\Testing\Attributes\Expectation;
use LaraStrict\StrictMock\Testing\Contracts\FindAllClassesActionContract;
use PHPUnit\Framework\Assert;

#[Expectation(class: FindAllClassesActionContractExecuteExpectation::class)]
class FindAllClassesActionContractAssert extends AbstractExpectationAllInOne implements FindAllClassesActionContract
{
    /**
     * @param array<FindAllClassesActionContractExecuteExpectation|null> $expectations
     */
    function __construct(array $expectations = [])
    {
        parent::__construct();
        $this->setExpectations($expectations);
    }

    /**
     * @return array<class-string>
     */
    function execute(): array
    {
        $_expectation = $this->getExpectation(FindAllClassesActionContractExecuteExpectation::class);

        if (is_callable($_expectation->_hook)) {
            ($_expectation->_hook)($_expectation);
        }

        return $_expectation->return;
    }
}
