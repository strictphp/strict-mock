<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Dummy\Tests\Contracts;

use Closure;
use PHPUnit\Framework\Assert;
use StrictPhp\StrictMock\Testing\Assert\AbstractExpectationCallsMap;
use StrictPhp\StrictMock\Testing\Attributes\Expectation;
use Tests\StrictPhp\StrictMock\Feature\Dummy\App\Contracts\Foo;

#[Expectation(class: FooExpectation::class)]
final class FooAssert extends AbstractExpectationCallsMap implements Foo
{
    /**
     * @param array<FooExpectation|null> $bar
     */
    public function __construct(array $bar = [])
    {
        parent::__construct();
        $this->setExpectations(FooExpectation::class, $bar);
    }

    public function bar(string $a): int
    {
        $_expectation = $this->getExpectation(FooExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->a, $a, $_message);

        if ($_expectation->_hook !== null) {
            ($_expectation->_hook)($a, $_expectation);
        }

        return $_expectation->return;
    }

    public static function expectationBar(int $return, string $a, ?Closure $_hook = null): FooExpectation
    {
        return new FooExpectation($return, $a, $_hook);
    }
}
