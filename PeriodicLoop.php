<?php

namespace WizardLoop\Loop;

use Amp\DeferredFuture;
use Amp\Future;
use function Amp\async;
use function Amp\delay;
use Cron\CronExpression;

class PeriodicLoop extends GenericLoop
{
    private $interval;
    private $callback;
    private $maxTicks = null;
    private $tickCount = 0;
    private $cron = null;

    protected function runLoop(): Future
    {
        $this->deferred = new DeferredFuture();
        async(function () {
            $this->tickCount = 0;
            while ($this->running) {
                while ($this->paused) {
                    yield delay(0.01);
                }
                try {
                    $result = ($this->callback)();
                    if ($result instanceof Future) {
                        $result->await();
                    } elseif ($result instanceof \Generator) {
                        foreach ($result as $_) {}
                    }
                    if ($this->onTick) {
                        ($this->onTick)();
                    }
                } catch (\Throwable $e) {
                    if ($this->onError) {
                        ($this->onError)($e);
                    }
                }
                $this->tickCount++;
                if ($this->maxTicks !== null && $this->tickCount >= $this->maxTicks) {
                    $this->stop();
                    break;
                }
                $interval = is_callable($this->interval) ? ($this->interval)() : $this->interval;
                yield delay(max($interval, 0.001));
            }
            if ($this->deferred && !$this->deferred->isComplete()) {
                $this->deferred->complete(null);
            }
        });
        return $this->deferred->getFuture();
    }

    public function __construct($interval, callable $callback, callable $onTick = null, callable $onError = null, ?int $maxTicks = null)
    {
        if (is_string($interval)) {
            $this->cron = CronExpression::factory($interval);
            $this->interval = 1;
        } else {
            $this->interval = $interval;
        }
        $this->callback = $callback;
        $this->onTick = $onTick;
        $this->onError = $onError;
        $this->maxTicks = $maxTicks;
    }
}
