<?php

declare(strict_types=1);

namespace Tests\LaraStrict\StrictMock\Feature\Testing\Contracts;

use LaraStrict\StrictMock\Testing\Assert\AbstractExpectationCallsMap;
use LaraStrict\StrictMock\Testing\Attributes\Expectation;
use LaraStrict\StrictMock\Testing\Contracts\ComposerJsonServiceContract;
use PHPUnit\Framework\Assert;

#[Expectation(class: ComposerJsonServiceContractContentExpectation::class)]
#[Expectation(class: ComposerJsonServiceContractIsExistExpectation::class)]
class ComposerJsonServiceContractAssert extends AbstractExpectationCallsMap implements ComposerJsonServiceContract
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

        $_expectation->_hook !== null && ($_expectation->_hook)($path, $_expectation);

        return $_expectation->return;
    }

    public function isExist(string $basePath): bool
    {
        $_expectation = $this->getExpectation(ComposerJsonServiceContractIsExistExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->basePath, $basePath, $_message);

        $_expectation->_hook !== null && ($_expectation->_hook)($basePath, $_expectation);

        return $_expectation->return;
    }
}
