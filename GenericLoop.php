<?php

namespace WizardLoop\Loop;

use Amp\Future;
use function Amp\async;

/**
 * Class GenericLoop
 * Provides a base async loop for executing operations periodically or on demand, in background loops a-la threads.
 */
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
