<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Testing\Contracts;

use Closure;
use PHPUnit\Framework\Assert;

final class FinderFactoryContractAssert extends \StrictPhp\StrictMock\Testing\Assert\AbstractExpectationAllInOne implements \StrictPhp\StrictMock\Testing\Contracts\FinderFactoryContract
{
    /**
     * @param array<FinderFactoryContractCreateExpectation|null> $expectations
     */
    public function __construct(array $expectations = [])
    {
        parent::__construct();
        $this->setExpectations($expectations);
    }

    public function create(string $path): iterable
    {
        $_expectation = $this->getExpectation(FinderFactoryContractCreateExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->path, $path, $_message);

        $_expectation->_hook !== null && ($_expectation->_hook)($path, $_expectation);

        return $_expectation->return;
    }

    public static function expectationCreate(
        iterable $return,
        string $path,
        ?Closure $_hook = null,
    ): FinderFactoryContractCreateExpectation {
        return new FinderFactoryContractCreateExpectation($return, $path, $_hook);
    }
}

/**
 * @internal
 */
final class FinderFactoryContractCreateExpectation extends \StrictPhp\StrictMock\Testing\Expectation\AbstractExpectation
{
    /**
     * @param Closure(string,self):void|null $_hook
     */
    public function __construct(
        public iterable $return,
        public readonly string $path,
        public readonly ?Closure $_hook = null,
    ) {
    }
}
