<?php

use PHPUnit\Framework\TestCase;
use WizardLoop\Loop\PeriodicLoop;
use function Amp\async;
use function Amp\delay;

class PeriodicLoopTest extends TestCase
{
    public function testPeriodicExecution()
    {
        $calls = 0;
        $loop = new PeriodicLoop(0.001, function () use (&$calls) { $calls++; }, null, null, 3);

        async(function () use ($loop) {
            $loop->start();
            yield delay(0.1);
            $loop->stop();
        })->await();

        $this->assertGreaterThan(0, $calls, "PeriodicLoop should execute callback at least once");
        $this->assertLessThanOrEqual(3, $calls, "PeriodicLoop should not execute more than maxTicks");
    }

    public function testOnTickCallback()
    {
        $ticks = 0;
        $loop = new PeriodicLoop(0.001, function () {}, function () use (&$ticks) { $ticks++; }, null, 2);

        async(function () use ($loop) {
            $loop->start();
            yield delay(0.005);
            $loop->stop();
        })->await();

        $this->assertGreaterThan(0, $ticks, "onTick callback should be called at least once");
    }

    public function testErrorHandling()
    {
        $errorCalled = false;
        $loop = new PeriodicLoop(0.001, function () { throw new \Exception("fail"); }, null, function () use (&$errorCalled) { $errorCalled = true; }, 1);

        async(function () use ($loop) {
            $loop->start();
            yield delay(0.002);
            $loop->stop();
        })->await();

        $this->assertTrue($errorCalled, "onError callback should be called when an exception occurs.");
    }

    public function testStartStopIdempotence()
    {
        $loop = new PeriodicLoop(0.001, function () {});
        $loop->start();
        $loop->start(); 
        $loop->stop();
        $loop->stop(); 
        $this->assertFalse($loop->isRunning());
    }

    public function testCronSyntax()
    {
        $this->expectException(\InvalidArgumentException::class);
        new PeriodicLoop('*', function () {});
    }
}
