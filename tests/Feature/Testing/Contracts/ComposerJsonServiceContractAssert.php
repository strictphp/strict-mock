<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Testing\Contracts;

use Closure;
use PHPUnit\Framework\Assert;
use StrictPhp\StrictMock\Testing\Assert\AbstractExpectationCallsMap;
use StrictPhp\StrictMock\Testing\Attributes\Expectation;
use StrictPhp\StrictMock\Testing\Contracts\ComposerJsonServiceContract;

#[Expectation(class: ComposerJsonServiceContractContentExpectation::class)]
#[Expectation(class: ComposerJsonServiceContractIsExistExpectation::class)]
final class ComposerJsonServiceContractAssert extends AbstractExpectationCallsMap implements ComposerJsonServiceContract
{
    /**
     * @param array<ComposerJsonServiceContractContentExpectation|null> $content
     * @param array<ComposerJsonServiceContractIsExistExpectation|null> $isExist
     */
    public function __construct(array $content = [], array $isExist = [])
    {
        parent::__construct();
        $this->setExpectations(ComposerJsonServiceContractContentExpectation::class, $content);
        $this->setExpectations(ComposerJsonServiceContractIsExistExpectation::class, $isExist);
    }

    public function content(string $path): mixed
    {
        $_expectation = $this->getExpectation(ComposerJsonServiceContractContentExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->path, $path, $_message);

        if ($_expectation->_hook !== null) {
            ($_expectation->_hook)($path, $_expectation);
        }

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

        if ($_expectation->_hook !== null) {
            ($_expectation->_hook)($basePath, $_expectation);
        }

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
