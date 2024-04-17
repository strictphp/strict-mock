<?php

declare(strict_types=1);

namespace StrictPhp\StrictMock\Testing\Assert;

use StrictPhp\StrictMock\Testing\Expectation\AbstractExpectation;
use LogicException;

/**
 * @deprecated will be replaced by see
 * @see AbstractExpectationAllInOne
 */
abstract class AbstractExpectationCallsMap
{
    /**
     * @var array<class-string<AbstractExpectation>, array<AbstractExpectation>>
     */
    private array $_expectationMap = [];

    /**
     * @var array<class-string<AbstractExpectation>, int>
     */
    private array $_callStep = [];

    /**
     * Contains current call number.
     */
    private int $_currentDebugStep = 0;

    public function __construct()
    {
        AssertExpectationManager::getInstance()->register($this);
    }

    /**
     * @deprecated no replace
     */
    public function addExpectation(object $expectation): self
    {
        $this->_expectationMap[$expectation::class][] = $expectation;

        return $this;
    }

    public function assertCalled(): void
    {
        $errors = [];
        foreach ($this->_expectationMap as $class => $expectations) {
            $called = $this->_callStep[$class] ?? 0;
            $expected = count($expectations);
            if ($expected === $called) {
                continue;
            }

            $errors[] = sprintf('[%s] expected %d call/s but was called <%d> time/s', $class, $expected, $called);
        }

        if ($errors === []) {
            return;
        }

        throw new LogicException(implode(PHP_EOL, array_map(static fn (string $e) => $e, $errors)));
    }

    /**
     * @param class-string<AbstractExpectation> $class
     * @param array<AbstractExpectation|null>   $expectations
     */
    public function setExpectations(string $class, array $expectations): self
    {
        $this->_expectationMap[$class] = array_values(array_filter($expectations));
        $this->_callStep[$class] = 0;

        return $this;
    }

    /**
     * @template TExpectation
     *
     * @param class-string<TExpectation> $class
     *
     * @return TExpectation
     */
    protected function getExpectation(string $class)
    {
        $map = $this->_expectationMap[$class] ?? [];
        $callStep = $this->_callStep[$class] ?? 0;

        $this->_currentDebugStep = $callStep + 1;

        if (array_key_exists($callStep, $map) === false) {
            throw new LogicException($this->getDebugMessage($this->_currentDebugStep, 'not set', 2));
        }

        $this->_callStep[$class] = $this->_currentDebugStep;

        return $map[$callStep];
    }

    protected function getDebugMessage(int $callStep = null, string $reason = 'failed', int $debugLevel = 1): string
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
}
