<?php

declare(strict_types=1);

namespace LaraStrict\StrictMock\Testing\Assert;

use LaraStrict\StrictMock\Testing\Exceptions\LogicException;
use LaraStrict\StrictMock\Testing\Expectation\AbstractExpectation;

abstract class AbstractExpectationAllInOne
{
    /**
     * @var array<class-string<AbstractExpectation>, AbstractExpectation>
     */
    private array $_expectationMap = [];

    /**
     * Contains current call number.
     */
    private int $_currentDebugStep = 0;

    public function __construct()
    {
        AssertExpectationManager::getInstance()->register($this);
    }

    /**
     * @template TExpectation
     *
     * @param class-string<TExpectation> $class
     *
     * @return TExpectation
     */
    final protected function getExpectation(string $class)
    {
        if ($this->_expectationMap === []) {
            throw new LogicException($this->getDebugMessage(null, 'no expectations', 2));
        }

        $nextClass = reset($this->_expectationMap)::class;
        if ($nextClass !== $class) {
            // actual call is $class but expected is $nextClass
            throw new LogicException($this->getDebugMessage(null, 'not set', 2));
        }

        ++$this->_currentDebugStep;

        return array_shift($this->_expectationMap);
    }

    protected function getDebugMessage(?int $callStep = null, string $reason = 'failed', int $debugLevel = 1): string
    {
        $caller = debug_backtrace()[$debugLevel];

        return sprintf(
            'Expectation for [%s@%s] %s for a n (%s) call',
            $caller['class'] ?? static::class,
            $caller['function'],
            $reason,
            $callStep ?? $this->_currentDebugStep,
        );
    }

    /**
     * @param array<AbstractExpectation|null> $expectations
     */
    final protected function setExpectations(array $expectations): void
    {
        $this->_expectationMap = array_filter($expectations);
    }
}
