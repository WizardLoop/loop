<?php

use PHPUnit\Framework\TestCase;
use WizardLoop\Loop\PeriodicLoop;
use function Amp\async;

class PeriodicLoopTest extends TestCase
{
    public function testPeriodicExecution()
    {
        $calls = 0;
        $loop = new PeriodicLoop(0.01, function () use (&$calls) { $calls++; }, null, null, 3);

        async(function () use ($loop) {
            $loop->start();
            \Amp\delay(0.05);
            $loop->stop();
        });

        $this->assertLessThanOrEqual(3, $calls);
    }

    public function testCronSyntax()
    {
        $this->expectException(\InvalidArgumentException::class);
        // Please note! cron must have only 5 fields, not 6!
        $loop = new PeriodicLoop('* * * * *', function () { });
    }
}
