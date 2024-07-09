<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Testing\Contracts;

use Closure;
use PHPUnit\Framework\Assert;

final class FindAllGeneratedAssertClassesActionContractAssert extends \StrictPhp\StrictMock\Testing\Assert\AbstractExpectationAllInOne implements \StrictPhp\StrictMock\Testing\Contracts\FindAllGeneratedAssertClassesActionContract
{
    /**
     * @param array<FindAllGeneratedAssertClassesActionContractExecuteExpectation|null> $expectations
     */
    public function __construct(array $expectations = [])
    {
        parent::__construct();
        $this->setExpectations($expectations);
    }

    public function execute(?string $dir = null): \Generator
    {
        $_expectation = $this->getExpectation(FindAllGeneratedAssertClassesActionContractExecuteExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->dir, $dir, $_message);

        $_expectation->_hook !== null && ($_expectation->_hook)($dir, $_expectation);

        return $_expectation->return;
    }

    public static function expectationExecute(
        \Generator $return,
        ?string $dir = null,
        ?Closure $_hook = null,
    ): FindAllGeneratedAssertClassesActionContractExecuteExpectation {
        return new FindAllGeneratedAssertClassesActionContractExecuteExpectation($return, $dir, $_hook);
    }
}

/**
 * @internal
 */
final class FindAllGeneratedAssertClassesActionContractExecuteExpectation extends \StrictPhp\StrictMock\Testing\Expectation\AbstractExpectation
{
    /**
     * @param Closure(string|null,self):void|null $_hook
     */
    public function __construct(
        public \Generator $return,
        public readonly ?string $dir = null,
        public readonly ?Closure $_hook = null,
    ) {
    }
}
