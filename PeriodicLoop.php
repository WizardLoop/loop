<?php

namespace WizardLoop\Loop;

use Amp\Future;
use function Amp\async;
use Amp\Cancellation;
use function Amp\delay;
use Cron\CronExpression;

/**
 * Class PeriodicLoop
 * Executes a callback periodically in an async background loop.
 */
class PeriodicLoop extends GenericLoop
{
/**
 * @var float|callable|string
 */
    private $interval;
    private $callback;
    private $maxTicks = null;
    private $tickCount = 0;
    private $cron = null;

    /**
     * @param float|callable|string $interval Interval in seconds, a callable returning float, or a cron string
     * @param callable $callback The async callback to execute
     * @param callable|null $onTick Callback to execute when the loop ticks
     * @param callable|null $onError Callback to execute when an error occurs
     * @param int|null $maxTicks Maximum number of ticks before stopping
     */
    public function __construct($interval, callable $callback, callable $onTick = null, callable $onError = null, ?int $maxTicks = null)
    {
        if (is_string($interval)) {
            $this->cron = CronExpression::factory($interval);
            $this->interval = $interval;
        } else {
            $this->interval = $interval;
        }
        $this->callback = $callback;
        $this->onTick = $onTick;
        $this->onError = $onError;
        $this->maxTicks = $maxTicks;
    }

    protected function runLoop(): Future
    {
        return async(function () {
            $this->tickCount = 0;
            while ($this->running) {
                while ($this->paused) {
                    yield delay(0.05); // Wait while paused
                }
                try {
                    ($this->callback)();
                    if ($this->onTick) {
                        ($this->onTick)();
                    }
                } catch (\Throwable $e) {
                    if ($this->onError) {
                        ($this->onError)($e);
                    } else {
                        throw $e;
                    }
                }
                $this->tickCount++;
                if ($this->maxTicks !== null && $this->tickCount >= $this->maxTicks) {
                    $this->stop();
                    break;
                }
                if ($this->cron) {
                    $now = new \DateTimeImmutable();
                    $next = $this->cron->getNextRunDate($now);
                    $interval = $next->getTimestamp() - $now->getTimestamp() + ($next->format('u') - $now->format('u')) / 1e6;
                } else {
                    $interval = is_callable($this->interval) ? ($this->interval)() : $this->interval;
                }
                yield delay($interval);
            }
        });
    }
}
