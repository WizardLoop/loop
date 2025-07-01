<?php

use PHPUnit\Framework\TestCase;
use WizardLoop\Loop\GenericLoop;
use Amp\Future;
use function Amp\async;
use function Amp\delay;

class DummyLoop extends GenericLoop
{
    private $shouldThrow = false;
    private $ticks = 0;
    private $maxTicks = 2;

    public function __construct($shouldThrow = false, $maxTicks = 2)
    {
        $this->shouldThrow = $shouldThrow;
        $this->maxTicks = $maxTicks;
    }

    protected function runLoop(): Future
    {
        $this->deferred = new \Amp\DeferredFuture();
        async(function () {
            $this->ticks = 0;
            while ($this->running && $this->ticks < $this->maxTicks) {
                if ($this->paused) {
                    yield delay(0.001);
                    continue;
                }
                try {
                    if ($this->shouldThrow) {
                        throw new \Exception("fail!");
                    }
                    if ($this->onTick) {
                        ($this->onTick)();
                    }
                } catch (\Throwable $e) {
                    if ($this->onError) {
                        ($this->onError)($e);
                    }
                }
                $this->ticks++;
                yield delay(0.001);
            }
            if ($this->deferred && !$this->deferred->isComplete()) {
                $this->deferred->complete(null);
            }
        });
        return $this->deferred->getFuture();
    }
}

class GenericLoopTest extends TestCase
{
    public function testStartStop()
    {
        $loop = new DummyLoop(false, 1);

        async(function () use ($loop) {
            $loop->start();
            yield delay(0.005);
            $loop->stop();
        })->await();

        $this->assertFalse($loop->isRunning(), "Loop should not be running after stop()");
    }

    public function testEventHooks()
    {
        $started = false;
        $ticked = false;
        $stopped = false;

        $loop = new DummyLoop(false, 1);
        $loop->onStart(function () use (&$started) { $started = true; });
        $loop->onTick(function () use (&$ticked) { $ticked = true; });
        $loop->onStop(function () use (&$stopped) { $stopped = true; });

        async(function () use ($loop) {
            $loop->start();
            yield delay(0.005);
            $loop->stop();
        })->await();

        $this->assertTrue($started, "onStart should be called");
        $this->assertTrue($ticked, "onTick should be called");
        $this->assertTrue($stopped, "onStop should be called");
    }

    public function testErrorHandling()
    {
        $errorCaught = false;
        $loop = new DummyLoop(true, 1);
        $loop->onError(function ($e) use (&$errorCaught) {
            $errorCaught = true;
            $this->assertInstanceOf(\Exception::class, $e);
        });

        async(function () use ($loop) {
            $loop->start();
            yield delay(0.005);
            $loop->stop();
        })->await();

        $this->assertTrue($errorCaught, "onError callback should be called when exception is thrown");
    }

    public function testPauseResume()
    {
        $ticks = 0;
        $loop = new DummyLoop(false, 3);
        $loop->onTick(function () use (&$ticks) { $ticks++; });

        async(function () use ($loop) {
            $loop->start();
            yield delay(0.002);
            $loop->pause();
            $countAtPause = $ticks;
            yield delay(0.005);
            $loop->resume();
            yield delay(0.005);
            $loop->stop();
            $this->assertEquals($countAtPause, $ticks, "No new ticks should happen during pause");
        })->await();
    }
}
