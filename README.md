# WizardLoop Loop

[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Made with ❤️](https://img.shields.io/badge/Made%20with-%E2%9D%A4%EF%B8%8F-blue)](https://github.com/WizardLoop/loop)
[![Code Style](https://img.shields.io/badge/Code_Style-PSR--12-blue.svg)](https://www.php-fig.org/psr/psr-12/)
[![Tests](https://img.shields.io/badge/Tests-PHPUnit-6DB33F?logo=phpunit)](https://phpunit.de/)

---

> **WizardLoop Loop** is a modern PHP async loop library built on [amphp](https://amphp.org/), providing powerful, flexible, and safe background loop APIs for periodic, on-demand, and cron-based execution—ideal for daemons, schedulers, and async workers.

---

## 🚀 Features at a Glance

| Feature                   | Description                                                             |
|---------------------------|-------------------------------------------------------------------------|
| Async Loops               | Run background operations with amphp's event loop                       |
| Periodic Execution        | Execute callbacks at fixed, dynamic, or cron-based intervals            |
| Pausing & Resuming        | Pause and resume loops at runtime                                       |
| Max Ticks                 | Limit the number of executions                                          |
| Event Hooks               | Attach callbacks for start, tick, stop, and error events                |
| Error Handling            | Robust error capture and custom error hooks                             |
| Custom Scheduling         | Use callables or cron syntax for advanced scheduling (5-field cron only) |
| **Async/generator support** | Callbacks can be async/generator or sync functions                      |

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

$loop = new PeriodicLoop(2.0, function () {
    echo "Tick: " . time() . "\n";
});
$loop->start();
// ... do other async work ...
$loop->stop();
```

### Custom Generic Loop Example
```php
use WizardLoop\Loop\GenericLoop;
use function Amp\async;

class MyLoop extends GenericLoop {
    protected function runLoop(): \Amp\Future {
        return async(function () {
            while ($this->running) {
                // Your async logic here
            }
        });
    }
}
```

---

## 🎛️ Advanced Usage

### Event Hooks & Error Handling
Attach event hooks for full control:

```php
$loop->onStart(fn() => echo "Started!\n");
$loop->onTick(fn() => echo "Ticked!\n");
$loop->onStop(fn() => echo "Stopped!\n");
$loop->onError(fn($e) => echo "Error: {$e->getMessage()}\n");
```

### Pausing & Resuming
```php
$loop->pause();  // Pauses the loop
$loop->resume(); // Resumes ticking
```

### Max Ticks
```php
$loop = new PeriodicLoop(1.0, function () {
    echo "Tick\n";
}, null, null, 5); // Will stop after 5 ticks
```

### Custom Scheduling
```php
$intervals = [1.0, 2.0, 3.0];
$loop = new PeriodicLoop(function () use (&$intervals) {
    return array_shift($intervals) ?? 5.0;
}, function () {
    echo "Tick\n";
});
```

### Cron Syntax
> **Note:** Only 5-field cron syntax is supported, as in standard Unix cron. (e.g. `* * * * *` for every minute)

```php
$loop = new PeriodicLoop('* * * * *', function () {
    echo "Tick every minute!\n";
});
```

If you want to tick every second, use an interval of `1.0` seconds:
```php
$loop = new PeriodicLoop(1.0, function () {
    echo "Tick every second!\n";
});
```

### Async/Generator Callback Example
The callback can be asynchronous:
```php
use function Amp\delay;

$loop = new PeriodicLoop(0.5, function () use (&$calls) {
    $calls++;
    yield delay(0.01); // Fully async tick!
});
```

---

## 🧪 Running Tests

```bash
composer install
vendor/bin/phpunit
```

---

## 🤝 Contributing

Pull requests and issues are welcome! Please ensure new features include tests and documentation.

---

### 🆕 Version 2.0.0 – Major Update

- **Async engine migrated to [amphp/amp v3](https://amphp.org/)**
- **Core loop logic now uses DeferredFuture and true Future for reliable async support**
- **stop() always awaits loop termination**
- **Async/generator callbacks fully supported in all loops**
- **
