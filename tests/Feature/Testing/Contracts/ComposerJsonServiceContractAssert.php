<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature\Testing\Contracts;

use Closure;
use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use LaraStrict\StrictMock\Testing\Attributes\Expectation;
use LaraStrict\StrictMock\Testing\Contracts\ComposerJsonServiceContract;
use PHPUnit\Framework\Assert;

#[Expectation(class: ComposerJsonServiceContractContentExpectation::class)]
#[Expectation(class: ComposerJsonServiceContractIsExistExpectation::class)]
final class ComposerJsonServiceContractAssert extends AbstractExpectationAllInOne implements ComposerJsonServiceContract
{
    /**
     * @param array<ComposerJsonServiceContractContentExpectation|ComposerJsonServiceContractIsExistExpectation|null> $expectations
     */
    public function __construct(array $expectations = [])
    {
        parent::__construct();
        $this->setExpectations($expectations);
    }

    public function content(string $path): mixed
    {
        $_expectation = $this->getExpectation(ComposerJsonServiceContractContentExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->path, $path, $_message);

        $_expectation->_hook !== null && ($_expectation->_hook)($path, $_expectation);

        return $_expectation->return;
    }

    public static function expectationContent(
        mixed $return,
        string $path,
        ?Closure $_hook = null,
    ): ComposerJsonServiceContractContentExpectation {
        return new ComposerJsonServiceContractContentExpectation($return, $path, $_hook);
    }

    public function isExist(string $basePath): bool
    {
        $_expectation = $this->getExpectation(ComposerJsonServiceContractIsExistExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->basePath, $basePath, $_message);

        $_expectation->_hook !== null && ($_expectation->_hook)($basePath, $_expectation);

        return $_expectation->return;
    }

    public static function expectationIsExist(
        bool $return,
        string $basePath,
        ?Closure $_hook = null,
    ): ComposerJsonServiceContractIsExistExpectation {
        return new ComposerJsonServiceContractIsExistExpectation($return, $basePath, $_hook);
    }
}
