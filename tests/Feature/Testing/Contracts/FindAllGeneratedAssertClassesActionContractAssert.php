<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Testing\Contracts;

use Generator;
use Closure;
use PHPUnit\Framework\Assert;
use StrictPhp\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use StrictPhp\StrictMock\Testing\Attributes\Expectation;
use StrictPhp\StrictMock\Testing\Contracts\FindAllGeneratedAssertClassesActionContract;

#[Expectation(class: FindAllGeneratedAssertClassesActionContractExecuteExpectation::class)]
final class FindAllGeneratedAssertClassesActionContractAssert extends AbstractExpectationAllInOne implements FindAllGeneratedAssertClassesActionContract
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

        if ($_expectation->_hook !== null) {
            ($_expectation->_hook)($dir, $_expectation);
        }

        return $_expectation->return;
    }

    public static function expectationExecute(
        Generator $return,
        ?string $dir = null,
        ?Closure $_hook = null,
    ): FindAllGeneratedAssertClassesActionContractExecuteExpectation {
        return new FindAllGeneratedAssertClassesActionContractExecuteExpectation($return, $dir, $_hook);
    }
}
