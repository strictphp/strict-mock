<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Testing\Contracts;

use ReflectionClass;
use Closure;
use PHPUnit\Framework\Assert;
use StrictPhp\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use StrictPhp\StrictMock\Testing\Attributes\Expectation;
use StrictPhp\StrictMock\Testing\Contracts\ReflectionClassFactoryContract;

#[Expectation(class: ReflectionClassFactoryContractCreateExpectation::class)]
final class ReflectionClassFactoryContractAssert extends AbstractExpectationAllInOne implements ReflectionClassFactoryContract
{
    /**
     * @param array<ReflectionClassFactoryContractCreateExpectation|null> $expectations
     */
    public function __construct(array $expectations = [])
    {
        parent::__construct();
        $this->setExpectations($expectations);
    }

    public function create(string $classOrPath): ReflectionClass
    {
        $_expectation = $this->getExpectation(ReflectionClassFactoryContractCreateExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->classOrPath, $classOrPath, $_message);

        if ($_expectation->_hook !== null) {
            ($_expectation->_hook)($classOrPath, $_expectation);
        }

        return $_expectation->return;
    }

    public static function expectationCreate(
        ReflectionClass $return,
        string $classOrPath,
        ?Closure $_hook = null,
    ): ReflectionClassFactoryContractCreateExpectation {
        return new ReflectionClassFactoryContractCreateExpectation($return, $classOrPath, $_hook);
    }
}
