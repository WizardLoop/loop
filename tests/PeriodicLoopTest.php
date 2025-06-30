<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use WizardLoop\Loop\PeriodicLoop;
use function Amp\delay;
use Amp\Loop;

class PeriodicLoopTest extends TestCase
{
    public function testPeriodicExecution(): void
    {
        $count = 0;
        $loop = new PeriodicLoop(0.1, function () use (&$count) {
            $count++;
        });

        $loop->start();
        \Amp\Future\await(delay(0.35)); // Let it tick a few times
        $loop->stop();
        \Amp\Future\await(delay(0.15)); // Ensure it stops

        $this->assertGreaterThanOrEqual(3, $count, 'Should tick at least 3 times');
        $this->assertLessThan(6, $count, 'Should not tick too many times');
    }

    public function testStartStopIdempotence(): void
    {
        $loop = new PeriodicLoop(0.1, function () {});
        $loop->start();
        $loop->start(); // Should not start twice
        $this->assertTrue($loop->isRunning());
        $loop->stop();
        $this->assertFalse($loop->isRunning());
        $loop->stop(); // Should not error
        $this->assertFalse($loop->isRunning());
    }

    public function testEventHooks(): void
    {
        $events = [];
        $loop = new PeriodicLoop(0.05, function () {},
            function () use (&$events) { $events[] = 'tick'; },
            function () use (&$events) { $events[] = 'error'; }
        );
        $loop->onStart(function () use (&$events) { $events[] = 'start'; });
        $loop->onStop(function () use (&$events) { $events[] = 'stop'; });
        $loop->start();
        \Amp\Future\await(delay(0.12));
        $loop->stop();
        \Amp\Future\await(delay(0.06));
        $this->assertContains('start', $events);
        $this->assertContains('stop', $events);
        $this->assertGreaterThanOrEqual(2, count(array_filter($events, fn($e) => $e === 'tick')));
    }

    public function testErrorHandling(): void
    {
        $errorCaught = false;
        $loop = new PeriodicLoop(0.05, function () { throw new \Exception('fail'); },
            null,
            function ($e) use (&$errorCaught) { $errorCaught = $e instanceof \Exception && $e->getMessage() === 'fail'; }
        );
        $loop->start();
        \Amp\Future\await(delay(0.07));
        $loop->stop();
        $this->assertTrue($errorCaught, 'Error handler should catch thrown exception');
    }

    public function testPauseResume(): void
    {
        $count = 0;
        $loop = new PeriodicLoop(0.05, function () use (&$count) { $count++; });
        $loop->start();
        \Amp\Future\await(delay(0.12));
        $loop->pause();
        $pausedCount = $count;
        \Amp\Future\await(delay(0.12));
        $this->assertEquals($pausedCount, $count, 'Should not tick while paused');
        $loop->resume();
        \Amp\Future\await(delay(0.12));
        $loop->stop();
        $this->assertGreaterThan($pausedCount, $count, 'Should tick after resuming');
    }

    public function testCustomScheduler(): void
    {
        $count = 0;
        $intervals = [0.05, 0.1, 0.15];
        $loop = new PeriodicLoop(function () use (&$intervals) {
            return array_shift($intervals) ?? 0.2;
        }, function () use (&$count) { $count++; });
        $loop->start();
        \Amp\Future\await(delay(0.35));
        $loop->stop();
        $this->assertGreaterThanOrEqual(3, $count, 'Should tick at least 3 times with custom intervals');
    }

    public function testMaxTicks(): void
    {
        $count = 0;
        $loop = new PeriodicLoop(0.05, function () use (&$count) { $count++; }, null, null, 3);
        $loop->start();
        \Amp\Future\await(delay(0.3));
        $this->assertEquals(3, $count, 'Should stop after maxTicks');
        $this->assertFalse($loop->isRunning(), 'Loop should not be running after maxTicks');
    }

    public function testCronSyntax(): void
    {
        $count = 0;
        $cron = '* * * * * *'; // every second
        $loop = new PeriodicLoop($cron, function () use (&$count) { $count++; });
        $loop->start();
        \Amp\Future\await(delay(2.2));
        $loop->stop();
        $this->assertGreaterThanOrEqual(2, $count, 'Should tick at least twice with cron syntax');
    }
} 
