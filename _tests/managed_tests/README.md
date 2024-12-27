# QIT Self-Tests

These self-tests validate that the **Quality Insights Toolkit (QIT)** flags expected issues for various WordPress environments and configurations.

## Overview

1. **Discovery & Packaging**
  - Each **test type** (e.g., `activation`, `security`, `woo-e2e`) has one or more **test scenarios** (e.g., `test-1`, `scenario-a`).
  - The script packages each scenario’s System Under Test (SUT) into a ZIP (unless reusing JSON snapshots).

2. **Dispatch**
  - The script uses `qit` to **run** each scenario test asynchronously, collecting `test_run_id`s.

3. **Polling**
  - Once dispatch is complete, the script periodically calls `qit get-multiple <test_run_id, ...>` to retrieve the status for all tests until each finishes.

4. **Snapshot Verification**
  - Each test’s final JSON output is compared against a **snapshot** with PHPUnit.
  - Mismatches fail, unless snapshots are being updated.

5. **Final Summary**
  - A pass/fail summary appears. If any test fails, the script exits with a non-zero code.

---

## Directory Layout

```

├── qit            (the QIT binary)
└── _tests/
    └── managed_tests/
        ├── QITSelfTests.php        (main script)
        ├── src/
        │   ├── Context.php
        │   ├── Logger.php
        │   ├── Config.php
        │   ├── Validator.php
        │   ├── TestManager.php
        │   ├── ZipManager.php
        │   ├── PhpUnitRunner.php
        │   ├── QitRunner.php
        │   ├── QITLiveOutput.php
        │   └── test-result-parser.php
        ├── activation/
        │   ├── test-1/
        │   │   ├── env.php
        │   │   ├── ...
        │   └── test-2/
        │       └── env.php
        ├── security/
        │   └── scenario-a/
        │       └── env.php
        └── woo-e2e/
            ├── scenario-1/
            └── scenario-2/
```

- **`QITSelfTests.php`** is the primary entry point for running or updating tests.
- Each **test type** directory (e.g., `activation/`) contains one or more subdirectories, each representing a scenario (with an `env.php` and SUT files).

---

## Running Tests

1. **Navigate** to `_tests/managed_tests`.
2. **Execute** the test script:
   ```bash
   php QITSelfTests.php
   ```
   By default, this:
  - Scans test types and scenarios.
  - Generates ZIPs for each SUT.
  - Dispatches tests to the local QIT CLI at `qit`.
  - Polls for test results until done.
  - Uses PHPUnit snapshots to verify results.

### Update Snapshots

```bash
php QITSelfTests.php update
```
Any snapshot mismatches are replaced with new snapshots (e.g., for changed output). Review and commit them if correct.

### Filter by Test Type

```bash
php QITSelfTests.php run activation
```
Runs only the `activation` tests. Multiple types can be comma-separated:
```bash
php QITSelfTests.php run activation,security
```

### Filter by Scenario

```bash
php QITSelfTests.php run activation test-1
```
Runs **only** `activation/test-1`.

### Environment Filters

```bash
php QITSelfTests.php run activation test-1 --env_filter=wp=6.3 --env_filter=php=8.0
```
Only matches scenarios where `env.php` indicates WP 6.3 **and** PHP 8.0.

---

## Additional Options

- **`QIT_SKIP_E2E=yes`**  
  Excludes the `woo-e2e` test type.

- **`QIT_REUSE_JSON=1`**  
  Skips dispatching to QIT and reuses existing JSON for local snapshot checks. This is useful to work on tht self-test script itself, as in how it parses the result JSON, etc. Run it once to generate the JSON results, run it again to re-use them.

- **Debug Logging**
  ```bash
  php QITSelfTests.php --debug
  ```
  Increases verbosity in the logs (`last-self-test.log` or similar).

---

## Files Breakdown

- **Context.php**: Maintains global state (test types to run, debug mode, etc.).
- **Logger.php**: Logs script operations to a file.
- **Config.php**: Parses CLI parameters (`--debug`, `--env_filter`, etc.).
- **Validator.php**: Checks for the QIT binary and other prerequisites.
- **TestManager.php**: Gathers test directories and scenarios, applying filters if needed.
- **ZipManager.php**: Creates `.zip` packages for each scenario.
- **PhpUnitRunner.php**: Generates and runs snapshot test files using PHPUnit.
- **QitRunner.php**: Dispatches tests (collects `test_run_id`) and polls them via `qit get-multiple`.
- **QITLiveOutput.php**: Handles interactive output, displaying test statuses and final summary.
- **test-result-parser.php**: Normalizes or removes sensitive data from raw JSON before snapshot comparison.

---

## Adding a New Test

1. **Create a Test-Type Directory**  
   For example: `performance/` inside `_tests/managed_tests/`.

2. **Add a Scenario**
   ```bash
   performance/test-1/env.php
   performance/test-1/<SUT files...>
   ```
   `env.php` defines environment parameters (PHP version, WP version, etc.).

3. **Run It**
   ```bash
   php QITSelfTests.php run performance
   ```
   It’ll be zipped, dispatched, polled, and the JSON verified against a snapshot.

---

## Common Questions

1. **“QIT binary not found”**:  
   Make sure `qit` exists and is accessible. The script checks that location via `Validator.php`.

2. **Reusing Existing JSON**:  
   If you want to skip the actual QIT run and just re-check local snapshots:
   ```bash
   QIT_REUSE_JSON=1 php QITSelfTests.php update
   ```

3. **Log Output**:  
   A detailed log is written each time in the same directory (e.g., `last-self-test.log`), containing verbose debug info.

---