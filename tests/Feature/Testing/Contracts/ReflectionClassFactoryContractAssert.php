<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Testing\Contracts;

use Closure;
use PHPUnit\Framework\Assert;

final class ReflectionClassFactoryContractAssert extends \StrictPhp\StrictMock\Testing\Assert\AbstractExpectationAllInOne implements \StrictPhp\StrictMock\Testing\Contracts\ReflectionClassFactoryContract
{
    /**
     * @param array<ReflectionClassFactoryContractCreateExpectation|null> $expectations
     */
    public function __construct(array $expectations = [])
    {
        parent::__construct();
        $this->setExpectations($expectations);
    }

    public function create(string $classOrPath): \ReflectionClass
    {
        $_expectation = $this->getExpectation(ReflectionClassFactoryContractCreateExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->classOrPath, $classOrPath, $_message);

        $_expectation->_hook !== null && ($_expectation->_hook)($classOrPath, $_expectation);

        return $_expectation->return;
    }

    public static function expectationCreate(
        \ReflectionClass $return,
        string $classOrPath,
        ?Closure $_hook = null,
    ): ReflectionClassFactoryContractCreateExpectation {
        return new ReflectionClassFactoryContractCreateExpectation($return, $classOrPath, $_hook);
    }
}

/**
 * @internal
 */
final class ReflectionClassFactoryContractCreateExpectation extends \StrictPhp\StrictMock\Testing\Expectation\AbstractExpectation
{
    /**
     * @param Closure(string,self):void|null $_hook
     */
    public function __construct(
        public \ReflectionClass $return,
        public readonly string $classOrPath,
        public readonly ?Closure $_hook = null,
    ) {
    }
}
