<?php

namespace WizardLoop\Loop;

use Amp\Cancellation;
use Amp\DeferredFuture;
use Amp\Future;

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

    /**
     * Attach a callback to be called when the loop starts.
     */
    public function onStart(callable $callback): void
    {
        $this->onStart = $callback;
    }

    /**
     * Attach a callback to be called on each tick (subclass must call it).
     */
    public function onTick(callable $callback): void
    {
        $this->onTick = $callback;
    }

    /**
     * Attach a callback to be called when the loop stops.
     */
    public function onStop(callable $callback): void
    {
        $this->onStop = $callback;
    }

    /**
     * Attach a callback to be called when an error occurs in the loop.
     */
    public function onError(callable $callback): void
    {
        $this->onError = $callback;
    }

    /**
     * Start the loop in the background.
     */
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

    /**
     * Stop the loop gracefully.
     */
    public function stop(): void
    {
        $this->running = false;
        if ($this->onStop) {
            ($this->onStop)();
        }
    }

    /**
     * The main loop logic. Should be implemented by subclasses.
     */
    abstract protected function runLoop(): Future;

    /**
     * Check if the loop is running.
     */
    public function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Pause the loop (subclass must honor this in runLoop).
     */
    public function pause(): void
    {
        $this->paused = true;
    }

    /**
     * Resume the loop if paused.
     */
    public function resume(): void
    {
        $this->paused = false;
    }

    /**
     * Check if the loop is paused.
     */
    public function isPaused(): bool
    {
        return $this->paused;
    }
} 