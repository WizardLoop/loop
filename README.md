# WizardLoop Loop

[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Made with ❤️](https://img.shields.io/badge/Made%20with-%E2%9D%A4%EF%B8%8F-blue)](https://github.com/WizardLoop/loop)
[![Code Style](https://img.shields.io/badge/Code_Style-PSR--12-blue.svg)](https://www.php-fig.org/psr/psr-12/)

---

> **WizardLoop Loop** is a modern PHP async loop library powered by [amphp/amp v3](https://amphp.org/), designed for safe and flexible background job execution, daemon tasks, schedulers, and async bots.  
> Easily run periodic/cyclic jobs, attach event hooks, and use cron or custom intervals — all with true async and pause/resume support.

---

## 🚀 Features

- 🌀 **Async Loops:** Built on Amp v3's event-loop engine for true async/await.
- ⏱ **Periodic, Dynamic, or Cron-based Intervals:** Supports simple seconds, callables, and classic 5-field cron.
- 🕹 **Pause & Resume:** Instantly pause/resume background loops at runtime.
- 🛑 **Safe Stop/Start:** Loops can be safely started and stopped, even inside an async context.
- 🏷 **Max Ticks:** Stop a loop after a set number of executions.
- 🎛 **Event Hooks:** `onStart`, `onTick`, `onStop`, and `onError` hooks for custom logic.
- ⚡ **Robust Error Handling:** All exceptions flow through your error callback.
- 🧩 **Customizable Base:** Extend `GenericLoop` for advanced patterns.

---

## 📦 Installation

```bash
composer require wizardloop/loop
```

---

## 🧙‍♂️ Quick Start

### Periodic Loop Example

```php
use WizardLoop\Loop\PeriodicLoop;

require 'vendor/autoload.php';

$loop = new PeriodicLoop(2.0, function () {
    echo "Tick: " . time() . "\n";
});

$loop->onStart(fn() => echo "Loop started!\n");
$loop->onTick(fn() => echo "Ticked!\n");
$loop->onStop(fn() => echo "Loop stopped!\n");
$loop->onError(fn($e) => echo "Error: {$e->getMessage()}\n");

$loop->start();

// Example: Stop the loop after 5 seconds (Amp async context required)
Amp\async(function () use ($loop) {
    yield Amp\delay(5);
    $loop->stop();
});
```

---

### Custom Loop Example

```php
use WizardLoop\Loop\GenericLoop;
use function Amp\async;
use function Amp\delay;

class MyLoop extends GenericLoop {
    protected function runLoop(): \Amp\Future {
        $this->deferred = new \Amp\DeferredFuture();
        async(function () {
            while ($this->running) {
                // Your async logic here
                yield delay(1);
            }
            if ($this->deferred && !$this->deferred->isComplete()) {
                $this->deferred->complete(null);
            }
        });
        return $this->deferred->getFuture();
    }
}
```

---

## 🎛️ Advanced Usage

- **Dynamic Intervals:**  
  Pass a callable to `PeriodicLoop` for variable timing:
  ```php
  $intervals = [1.0, 2.0, 5.0];
  $loop = new PeriodicLoop(function () use (&$intervals) {
      return array_shift($intervals) ?? 10.0;
  }, function () {
      echo "Tick!\n";
  });
  ```
- **Cron Support:**  
  Use classic 5-field cron syntax (e.g. `* * * * *` for every minute):
  ```php
  $loop = new PeriodicLoop('* * * * *', function () {
      echo "Tick every minute!\n";
  });
  ```
- **Pause & Resume:**
  ```php
  $loop->pause();
  $loop->resume();
  ```

---

## 🧪 Testing & Development

> **Note:**  
> Unit tests require [PHPUnit 10+](https://phpunit.de/) and are fully async (Amp v3).  
> You do **not** need the tests to use the library in production.

```bash
composer install
vendor/bin/phpunit
```

---

## 📜 License

[MIT License](LICENSE)

---

## 🤝 Contributing

PRs and issues are welcome!  
For questions and feature requests, contact [@wizardloop](https://wizardloop.t.me/)  
or open an issue on [GitHub](https://github.com/WizardLoop/loop).

---

## 🆕 Recent Changes

- Full Amp v3 async engine, hooks, and deferred support
- Safer pause/resume/stop behavior
- 5-field cron only (not 6-field/secondly)
- Strong async test coverage (for devs/CI)
