# WizardLoop Loop

[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Made with ❤️](https://img.shields.io/badge/Made%20with-%E2%9D%A4%EF%B8%8F-blue)](https://github.com/WizardLoop/loop)
[![Code Style](https://img.shields.io/badge/Code_Style-PSR--12-blue.svg)](https://www.php-fig.org/psr/psr-12/)
[![Tests](https://img.shields.io/badge/Tests-PHPUnit-6DB33F?logo=phpunit)](https://phpunit.de/)

---

> **WizardLoop Loop** is a modern PHP async loop library built on [amphp](https://amphp.org/), providing powerful, flexible, and safe background loop APIs for periodic, on-demand, and cron-based execution—ideal for daemons, schedulers, and async workers.

---

## 🚀 Features at a Glance

| Feature                | Description                                                                 |
|------------------------|-----------------------------------------------------------------------------|
| Async Loops            | Run background operations with amphp's event loop                            |
| Periodic Execution     | Execute callbacks at fixed, dynamic, or cron-based intervals                 |
| Pausing & Resuming     | Pause and resume loops at runtime                                            |
| Max Ticks              | Limit the number of executions                                               |
| Event Hooks            | Attach callbacks for start, tick, stop, and error events                     |
| Error Handling         | Robust error capture and custom error hooks                                  |
| Custom Scheduling      | Use callables or cron syntax for advanced scheduling                         |

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
use Amp\Future;

class MyLoop extends GenericLoop {
    protected function runLoop(): Future {
        return Future::spawn(function () {
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
```php
$loop = new PeriodicLoop('* * * * * *', function () {
    echo "Tick every second!\n";
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
