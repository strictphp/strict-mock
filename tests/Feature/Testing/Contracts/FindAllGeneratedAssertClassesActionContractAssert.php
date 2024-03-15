<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature\Testing\Contracts;

use Generator;
use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use LaraStrict\StrictMock\Testing\Attributes\Expectation;
use LaraStrict\StrictMock\Testing\Contracts\FindAllGeneratedAssertClassesActionContract;
use PHPUnit\Framework\Assert;

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

    public function execute(?string $dir = null): Generator
    {
        $_expectation = $this->getExpectation(FindAllGeneratedAssertClassesActionContractExecuteExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->dir, $dir, $_message);

        $_expectation->_hook !== null && ($_expectation->_hook)($dir, $_expectation);

        return $_expectation->return;
    }
}
