<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature\Testing\Contracts;

use Closure;
use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use LaraStrict\StrictMock\Testing\Attributes\Expectation;
use LaraStrict\StrictMock\Testing\Contracts\FinderFactoryContract;
use PHPUnit\Framework\Assert;

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

        if ($_expectation->_hook !== null) {
            ($_expectation->_hook)($path, $_expectation);
        }

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
