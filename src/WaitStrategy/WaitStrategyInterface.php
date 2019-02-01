<?php declare(strict_types=1);

namespace App\WaitStrategy;

interface WaitStrategyInterface
{
    /**
     * Return next wait period in seconds
     *
     * @return int
     */
    public function next() : int;

    /**
     * Reset
     */
    public function reset() : void;
}
