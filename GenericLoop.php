<?php

namespace WizardLoop\Loop;

use Amp\Future;
use function Amp\async;

abstract class GenericLoop
{
    protected bool $running = false;
    protected ?Future $loopFuture = null;
    protected $onStart = null;
    protected $onTick = null;
    protected $onStop = null;
    protected $onError = null;
    protected bool $paused = false;

    public function onStart(callable $callback): void
    {
        $this->onStart = $callback;
    }

    public function onTick(callable $callback): void
    {
        $this->onTick = $callback;
    }

    public function onStop(callable $callback): void
    {
        $this->onStop = $callback;
    }

    public function onError(callable $callback): void
    {
        $this->onError = $callback;
    }

    public function start(): void
    {
        if ($this->running) {
            return;
        }
        $this->running = true;
        if ($this->onStart) {
            ($this->onStart)();
        }
        $this->loopFuture = $this->runLoop();
    }

    public function stop(): void
    {
        $this->running = false;
        if ($this->onStop) {
            ($this->onStop)();
        }
        // ה־loopFuture מסתיים כשה־runLoop נגמר, אז מחכים לו
        if ($this->loopFuture) {
            $this->loopFuture->await();
            $this->loopFuture = null;
        }
    }

    abstract protected function runLoop(): Future;

    public function isRunning(): bool
    {
        return $this->running;
    }

    public function pause(): void
    {
        $this->paused = true;
    }

    public function resume(): void
    {
        $this->paused = false;
    }

    public function isPaused(): bool
    {
        return $this->paused;
    }
}
