<?php

use PHPUnit\Framework\TestCase;
use WizardLoop\Loop\GenericLoop;
use Amp\Future;
use function Amp\async;

class GenericLoopTest extends TestCase
{
    public function testStartStop()
    {
        $loop = $this->getMockForAbstractClass(GenericLoop::class);
        $this->assertFalse($loop->isRunning());
        $loop->start();
        $this->assertTrue($loop->isRunning());
        $loop->stop();
        $this->assertFalse($loop->isRunning());
    }

    public function testEventHooks()
    {
        $loop = $this->getMockForAbstractClass(GenericLoop::class);

        $started = false;
        $stopped = false;
        $loop->onStart(function () use (&$started) {
            $started = true;
        });
        $loop->onStop(function () use (&$stopped) {
            $stopped = true;
        });

        $loop->start();
        $this->assertTrue($started);

        $loop->stop();
        $this->assertTrue($stopped);
    }

    public function testErrorHandling()
    {
        $loop = $this->getMockForAbstractClass(GenericLoop::class);

        $errorCalled = false;
        $loop->onError(function () use (&$errorCalled) {
            $errorCalled = true;
        });

        // simulate error - this depends on your logic
        if ($loop->onError) {
            ($loop->onError)();
        }
        $this->assertTrue($errorCalled);
    }
}
