# 📦 Changelog

---

## 📦 Version v0.1.0 – Initial Release

First public release of **WizardLoop Loop** — a modern PHP async loop library built on [amphp](https://amphp.org/), ideal for daemons, schedulers, and background workers.

### ✨ Features

- ✅ **Periodic execution** using intervals, callbacks, or cron syntax  
- ✅ **Pause & resume** control at runtime  
- ✅ **Tick limit support** (`maxTicks`)  
- ✅ **Event hooks** for `start`, `tick`, `stop`, and `error`  
- ✅ **Custom loop base class** (`GenericLoop`) for advanced use cases  

---

📦 Install via:  
```bash
composer require wizardloop/loop
```

---

## ✨ WizardLoop Loop v2.0.0 — Major Async Upgrade! 🚀

### 🎉 What's New?

- **🚀 Async Engine Migration:**  
  All loop logic is now based on [amphp/amp v3](https://amphp.org/) for true async/await support.
- **🧩 Reliable Async with DeferredFuture:**  
  Loop operations use `DeferredFuture` and real `Future` objects — ensuring ticks/callbacks always run in a genuine async context.
- **🔄 Safe stop/start:**  
  Calling `stop()` now always waits (await) for the loop to finish before returning — safer for tests and production code.
- **🌀 Async/generator Callbacks:**  
  Loop callbacks can now be generators or async (yield/delay), not just synchronous functions.
- **⏰ Standard Cron Only:**  
  Only standard 5-field cron syntax is supported (e.g., `* * * * *` for every minute).
- **🧪 Stronger Test Coverage:**  
  Test suite is fully async-aware and validates real event-loop behavior in CI.
- **🧹 Code Modernization:**  
  Deprecated code (`Future::spawn`, legacy patterns) were replaced by modern `Amp\async`.
- **📖 Docs & Examples:**  
  All README and code examples were updated to reflect async best practices.

---

### ⚡️ Migration Notes

- No breaking changes for users of synchronous callbacks.
- You can now use async/generator callbacks in all loop types and event hooks.
- `stop()` and `start()` are safer and more predictable than ever.
- All public APIs are backward compatible.

---

### 💡 How to Upgrade

- Just update to the latest version via Composer:
  ```bash
  composer require wizardloop/loop:^2.0
  ```

---

### 🙌 Thanks & Feedback

- Feedback and contributions are very welcome!  
- ⭐ Star the repo, open issues, or suggest features at [github.com/WizardLoop/loop](https://github.com/WizardLoop/loop)

---

_Enjoy the new power and reliability of WizardLoop Loop!_

---

## 📦 Version v2.0.1 – Minor Improvements & Fixes

### 🛠 What's Changed?

- Improved documentation and usage examples in README.md
- Cleaned up async event loop handling for greater stability
- Tweaked internal checks for safer pause, resume, and stop behavior
- Enhanced code comments and PSR-12 formatting
- [DEV] Minor test improvements for async test coverage

---

### 🆕 Recent Changes (since 2.0.0)

- Full Amp v3 async engine, hooks, and deferred support
- Safer pause/resume/stop behavior
- 5-field cron only (not 6-field/secondly)
- Strong async test coverage (for devs/CI)
