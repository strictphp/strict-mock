<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Testing\Contracts;

use Closure;
use PHPUnit\Framework\Assert;
use StrictPhp\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use StrictPhp\StrictMock\Testing\Attributes\Expectation;
use StrictPhp\StrictMock\Testing\Contracts\ComposerPsr4ServiceContract;

#[Expectation(class: ComposerPsr4ServiceContractAutoloadExpectation::class)]
#[Expectation(class: ComposerPsr4ServiceContractAutoloadDevExpectation::class)]
#[Expectation(class: ComposerPsr4ServiceContractTryAllExpectation::class)]
final class ComposerPsr4ServiceContractAssert extends AbstractExpectationAllInOne implements ComposerPsr4ServiceContract
{
    /**
     * @param array<ComposerPsr4ServiceContractAutoloadExpectation|ComposerPsr4ServiceContractAutoloadDevExpectation|ComposerPsr4ServiceContractTryAllExpectation|null> $expectations
     */
    public function __construct(array $expectations = [])
    {
        parent::__construct();
        $this->setExpectations($expectations);
    }

    public function autoload(string $realPath): array
    {
        $_expectation = $this->getExpectation(ComposerPsr4ServiceContractAutoloadExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->realPath, $realPath, $_message);

        $_expectation->_hook !== null && ($_expectation->_hook)($realPath, $_expectation);

        return $_expectation->return;
    }

    public static function expectationAutoload(
        array $return,
        string $realPath,
        ?Closure $_hook = null,
    ): ComposerPsr4ServiceContractAutoloadExpectation {
        return new ComposerPsr4ServiceContractAutoloadExpectation($return, $realPath, $_hook);
    }

    public function autoloadDev(string $realPath): array
    {
        $_expectation = $this->getExpectation(ComposerPsr4ServiceContractAutoloadDevExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->realPath, $realPath, $_message);

        $_expectation->_hook !== null && ($_expectation->_hook)($realPath, $_expectation);

        return $_expectation->return;
    }

    public static function expectationAutoloadDev(
        array $return,
        string $realPath,
        ?Closure $_hook = null,
    ): ComposerPsr4ServiceContractAutoloadDevExpectation {
        return new ComposerPsr4ServiceContractAutoloadDevExpectation($return, $realPath, $_hook);
    }

    public function tryAll(string $realPath): \Generator
    {
        $_expectation = $this->getExpectation(ComposerPsr4ServiceContractTryAllExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->realPath, $realPath, $_message);

        $_expectation->_hook !== null && ($_expectation->_hook)($realPath, $_expectation);

        return $_expectation->return;
    }

    public static function expectationTryAll(
        \Generator $return,
        string $realPath,
        ?Closure $_hook = null,
    ): ComposerPsr4ServiceContractTryAllExpectation {
        return new ComposerPsr4ServiceContractTryAllExpectation($return, $realPath, $_hook);
    }
}
