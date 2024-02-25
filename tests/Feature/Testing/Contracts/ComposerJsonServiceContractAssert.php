<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature\Testing\Contracts;

use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationCallsMap;
use LaraStrict\StrictMock\Testing\Attributes\Expectation;
use LaraStrict\StrictMock\Testing\Contracts\ComposerJsonServiceContract;
use PHPUnit\Framework\Assert;

#[Expectation(class: ComposerJsonServiceContractIsExistExpectation::class)]
#[Expectation(class: ComposerJsonServiceContractContentExpectation::class)]
class ComposerJsonServiceContractAssert extends AbstractExpectationCallsMap implements ComposerJsonServiceContract
{
    /**
     * @param array<ComposerJsonServiceContractIsExistExpectation|null> $isExist
     * @param array<ComposerJsonServiceContractContentExpectation|null> $content
     */
    function __construct(array $isExist = [], array $content = [])
    {
        parent::__construct();
        $this->setExpectations(ComposerJsonServiceContractIsExistExpectation::class, $isExist);
        $this->setExpectations(ComposerJsonServiceContractContentExpectation::class, $content);
    }

    function isExist(string $basePath): bool
    {
        $_expectation = $this->getExpectation(ComposerJsonServiceContractIsExistExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->basePath, $basePath, $_message);

        if (is_callable($_expectation->_hook)) {
            ($_expectation->_hook)($basePath, $_expectation);
        }

        return $_expectation->return;
    }

    function content(string $path): mixed
    {
        $_expectation = $this->getExpectation(ComposerJsonServiceContractContentExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->path, $path, $_message);

        if (is_callable($_expectation->_hook)) {
            ($_expectation->_hook)($path, $_expectation);
        }

        return $_expectation->return;
    }
}
