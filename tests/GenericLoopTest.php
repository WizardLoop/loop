<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use WizardLoop\Loop\GenericLoop;
use Amp\Future;
use function Amp\delay;

class DummyLoop extends GenericLoop
{
    public int $ticks = 0;
    protected function runLoop(): Future
    {
        return Future::spawn(function () {
            while ($this->running) {
                $this->ticks++;
                yield delay(0.05);
            }
        });
    }
}

class ErrorLoop extends GenericLoop
{
    public $errorCaught = false;
    protected function runLoop(): Future
    {
        return Future::spawn(function () {
            try {
                throw new \RuntimeException('loop error');
            } catch (\Throwable $e) {
                if ($this->onError) {
                    ($this->onError)($e);
                    $this->errorCaught = true;
                } else {
                    throw $e;
                }
            }
        });
    }
}

class GenericLoopTest extends TestCase
{
    public function testStartStop(): void
    {
        $loop = new DummyLoop();
        $loop->start();
        \Amp\Future\await(delay(0.16));
        $loop->stop();
        $ticks = $loop->ticks;
        \Amp\Future\await(delay(0.1));
        $this->assertGreaterThanOrEqual(3, $ticks);
        $this->assertEquals($ticks, $loop->ticks, 'Should not tick after stop');
    }

    public function testEventHooks(): void
    {
        $events = [];
        $loop = new DummyLoop();
        $loop->onStart(function () use (&$events) { $events[] = 'start'; });
        $loop->onTick(function () use (&$events) { $events[] = 'tick'; });
        $loop->onStop(function () use (&$events) { $events[] = 'stop'; });
        $loop->start();
        \Amp\Future\await(delay(0.12));
        $loop->stop();
        \Amp\Future\await(delay(0.06));
        $this->assertContains('start', $events);
        $this->assertContains('stop', $events);
    }

    public function testErrorHandling(): void
    {
        $loop = new ErrorLoop();
        $caught = false;
        $loop->onError(function ($e) use (&$caught) {
            $caught = $e instanceof \RuntimeException && $e->getMessage() === 'loop error';
        });
        $loop->start();
        \Amp\Future\await(delay(0.05));
        $this->assertTrue($caught, 'Error handler should catch thrown exception');
    }
} 
