<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature\Testing\Contracts;

use Closure;
use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use LaraStrict\StrictMock\Testing\Attributes\Expectation;
use LaraStrict\StrictMock\Testing\Contracts\TestFrameworkServiceContract;
use PHPUnit\Framework\Assert;

#[Expectation(class: TestFrameworkServiceContractAssertClassExpectation::class)]
#[Expectation(class: TestFrameworkServiceContractAssertEqualsExpectation::class)]
final class TestFrameworkServiceContractAssert extends AbstractExpectationAllInOne implements TestFrameworkServiceContract
{
    /**
     * @param array<TestFrameworkServiceContractAssertClassExpectation|TestFrameworkServiceContractAssertEqualsExpectation|null> $expectations
     */
    public function __construct(array $expectations = [])
    {
        parent::__construct();
        $this->setExpectations($expectations);
    }

    public function assertClass(): string
    {
        $_expectation = $this->getExpectation(TestFrameworkServiceContractAssertClassExpectation::class);

        if ($_expectation->_hook !== null) {
            ($_expectation->_hook)($_expectation);
        }

        return $_expectation->return;
    }

    public static function expectationAssertClass(
        string $return,
        ?Closure $_hook = null,
    ): TestFrameworkServiceContractAssertClassExpectation {
        return new TestFrameworkServiceContractAssertClassExpectation($return, $_hook);
    }

    public function assertEquals(string $expected, string $actual, string $message): string
    {
        $_expectation = $this->getExpectation(TestFrameworkServiceContractAssertEqualsExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->expected, $expected, $_message);
        Assert::assertEquals($_expectation->actual, $actual, $_message);
        Assert::assertEquals($_expectation->message, $message, $_message);

        if ($_expectation->_hook !== null) {
            ($_expectation->_hook)($expected, $actual, $message, $_expectation);
        }

        return $_expectation->return;
    }

    public static function expectationAssertEquals(
        string $return,
        string $expected,
        string $actual,
        string $message,
        ?Closure $_hook = null,
    ): TestFrameworkServiceContractAssertEqualsExpectation {
        return new TestFrameworkServiceContractAssertEqualsExpectation($return, $expected, $actual, $message, $_hook);
    }
}
