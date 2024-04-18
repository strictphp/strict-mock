<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Testing\Contracts;

use Closure;
use PHPUnit\Framework\Assert;
use StrictPhp\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use StrictPhp\StrictMock\Testing\Attributes\Expectation;
use StrictPhp\StrictMock\Testing\Contracts\FinderFactoryContract;

#[Expectation(class: FinderFactoryContractCreateExpectation::class)]
final class FinderFactoryContractAssert extends AbstractExpectationAllInOne implements FinderFactoryContract
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
