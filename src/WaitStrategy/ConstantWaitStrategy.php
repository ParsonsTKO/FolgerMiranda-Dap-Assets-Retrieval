<?php declare(strict_types=1);

namespace App\WaitStrategy;

final class ConstantWaitStrategy implements WaitStrategyInterface
{
    /**
     * @var int
     */
    private $wait;

    /**
     * @param int $wait
     */
    public function __construct(int $wait)
    {
        $this->wait = $wait;
    }

    /**
     * {@inheritdoc}
     */
    public function next() : int
    {
        return $this->wait;
    }

    /**
     * Reset
     */
    public function reset() : void
    {
        // Do nothing
    }
}
