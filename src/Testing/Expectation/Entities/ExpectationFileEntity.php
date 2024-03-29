<?php declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Expectation\Entities;

use LaraStrict\StrictMock\Testing\Entities\ObjectEntity;
use Nette\PhpGenerator\Method;

final class ExpectationFileEntity
{
    public function __construct(
        public readonly ObjectEntity $object,
        public readonly Method $constructor,
    ) {

    }
}
