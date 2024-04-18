<?php

declare(strict_types=1);

namespace Tests\StrictPhp\StrictMock\Feature\Testing\Contracts;

use Closure;
use PHPUnit\Framework\Assert;
use StrictPhp\StrictMock\Testing\Assert\AbstractExpectationAllInOne;
use StrictPhp\StrictMock\Testing\Attributes\Expectation;
use StrictPhp\StrictMock\Testing\Contracts\FilePathToClassActionContract;

#[Expectation(class: FilePathToClassActionContractExecuteExpectation::class)]
final class FilePathToClassActionContractAssert extends AbstractExpectationAllInOne implements FilePathToClassActionContract
{
    /**
     * @param array<FilePathToClassActionContractExecuteExpectation|null> $expectations
     */
    public function __construct(array $expectations = [])
    {
        parent::__construct();
        $this->setExpectations($expectations);
    }

    public function execute(string $filepath): ?string
    {
        $_expectation = $this->getExpectation(FilePathToClassActionContractExecuteExpectation::class);
        $_message = $this->getDebugMessage();

        Assert::assertEquals($_expectation->filepath, $filepath, $_message);

        $_expectation->_hook !== null && ($_expectation->_hook)($filepath, $_expectation);

        return $_expectation->return;
    }

    public static function expectationExecute(
        ?string $return,
        string $filepath,
        ?Closure $_hook = null,
    ): FilePathToClassActionContractExecuteExpectation {
        return new FilePathToClassActionContractExecuteExpectation($return, $filepath, $_hook);
    }
}
