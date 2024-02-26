<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature\Testing\Contracts;

use Generator;
use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use LaraStrict\StrictMock\Testing\Attributes\Expectation;
use LaraStrict\StrictMock\Testing\Contracts\FindAllGeneratedAssertClassesActionContract;

#[Expectation(class: FindAllGeneratedAssertClassesActionContractExecuteExpectation::class)]
class FindAllGeneratedAssertClassesActionContractAssert extends AbstractExpectationAllInOne implements FindAllGeneratedAssertClassesActionContract
{
    /**
     * @param array<FindAllGeneratedAssertClassesActionContractExecuteExpectation|null> $expectations
     */
    public function __construct(array $expectations = [])
    {
        parent::__construct();
        $this->setExpectations($expectations);
    }

    /**
     * @return Generator<class-string>
     */
    public function execute(): Generator
    {
        $_expectation = $this->getExpectation(FindAllGeneratedAssertClassesActionContractExecuteExpectation::class);

        if (is_callable($_expectation->_hook)) {
            ($_expectation->_hook)($_expectation);
        }

        return $_expectation->return;
    }
}
