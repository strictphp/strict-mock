<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Dummy\Tests\Interfaces;

use Closure;
use PHPUnit\Framework\Assert;
use StrictPhp\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use StrictPhp\StrictMock\Testing\Attributes\Expectation;
use StrictPhp\StrictMock\Testing\Expectation\AbstractExpectation;
use Tests\StrictPhp\StrictMock\Feature\Dummy\App\Interfaces\Bar;

final class BarAssert extends AbstractExpectationAllInOne implements Bar
{
    /**
     * @param array<BarFooExpectation|null> $expectations
     */
    public function __construct(array $expectations = [])
    {
        parent::__construct();
        $this->setExpectations($expectations);
    }

    public function foo(string $a): int
    {
        $_expectation = $this->getExpectation(BarFooExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->a, $a, $_message);

        if ($_expectation->_hook !== null) {
            ($_expectation->_hook)($a, $_expectation);
        }

        return $_expectation->return;
    }

    public static function expectationFoo(int $return, string $a, ?Closure $_hook = null): BarFooExpectation
    {
        return new BarFooExpectation($return, $a, $_hook);
    }
}

final class BarFooExpectation extends AbstractExpectation
{
    /**
     * @param Closure(string,self):void|null $_hook
     */
    public function __construct(
        public readonly int $return,
        public readonly string $a,
        public readonly ?Closure $_hook = null,
    ) {
    }
}
