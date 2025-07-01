<?php

namespace WizardLoop\Loop;

use Amp\DeferredFuture;
use Amp\Future;

abstract class GenericLoop
{
    protected bool $running = false;
    protected ?Future $loopFuture = null;
    protected ?DeferredFuture $deferred = null;
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
        if (!$this->running) {
            retur
