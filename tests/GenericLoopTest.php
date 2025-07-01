<?php

use PHPUnit\Framework\TestCase;
use WizardLoop\Loop\GenericLoop;
use Amp\Future;
use function Amp\async;

class DummyLoop extends GenericLoop
{
    protected function runLoop(): Future
    {
        return async(fn() => null);
    }
}

class GenericLoopTest extends TestCase
{
    public function testStartStop()
    {
        $loop = new DummyLoop();
        $this->assertFalse($loop->isRunning());
        $loop->start();
        $this->assertTrue($loop->isRunning());
        $loop->stop();
        $this->assertFalse($loop->isRunning());
    }

    public function testEventHooks()
    {
        $loop = new DummyLoop();

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
        $loop = new DummyLoop();
        $errorCalled = false;

        $loop->onError(function () use (&$errorCalled) {
            $errorCalled = true;
        });

        $reflection = new \ReflectionClass($loop);
        if ($reflection->hasProperty('onError')) {
            $prop = $reflection->getProperty('onError');
            $prop->setAccessible(true);
            $cb = $prop->getValue($loop);
            if (is_callable($cb)) {
                $cb(new \Exception('test'));
            }
        }

        $this->assertTrue($errorCalled, "onError callback should be called.");
    }
}
