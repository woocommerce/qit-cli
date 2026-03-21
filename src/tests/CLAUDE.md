# QIT CLI Test Landscape

## How to Run

### Static Analysis (from `/storage/qit/qit-cli`)
```bash
make phpcs      # WordPress coding standards (includes PHPCBF auto-fix)
make phpstan    # Type safety / dead code
make phan       # Additional static analysis (qit-cli only)
```

### Unit Tests (from `/storage/qit/qit-cli/src`)
```bash
./vendor/bin/phpunit                    # 181 tests, ~2s
```

### Integration Tests (from `/storage/qit/qit-cli/src/tests/integration`)

**Fast (no Docker) — always use paratest:**
```bash
./vendor/bin/paratest --stop-on-failure --stop-on-error --exclude-group docker tests/SomeDir/
```

**Docker tests (slow, 40-120s each — run individually):**
```bash
./vendor/bin/phpunit --group docker --filter test_name_here
```

**Important:** Always use `--stop-on-failure --stop-on-error` with paratest. We patch paratest
(via `cweagans/composer-patches`) to actually kill workers on failure and enforce a 5-minute
per-test timeout. See `patches/paratest-stop-on-failure-timeout.patch`.

### Manager Tests (from `/storage/qit/qit-manager`)
```bash
# 703 tests, separate repo
```

## Integration Test Directory Map

### Commands/ — ✅ VERIFIED 2026-03-21 (58 tests, 176 assertions)
Audited, stable. Tests command-level integration.

| File | Docker? | Last meaningful commit | What it tests |
|---|---|---|---|
| ExtensionResolutionTest | no | `8382081f` Resolve --woo and --wp | Slug/path/URL/symlink resolution |
| RunE2ECommandTest | yes (16 tests) | `1c7bea22` Make blobs optional | Full run:e2e workflows with Docker |
| RunGroupCommandTest | no | `94dff827` Move tests | Group command config |
| UpEnvironmentCommandTest | mixed (1 docker) | `0ee86c56` Use long version alias | env:up config + 1 real Docker verification |

### PreCommand/ — ⚠️ NEEDS AUDIT
Config resolution tests. Some pass, some fail. Need to audit each test against current code behavior
before fixing — the tests or the code may be wrong.

| File | Last meaningful commit | Status | Notes |
|---|---|---|---|
| EnvUpPrecedenceTest | `e1c2bfa6` Fix 3 failing tests | ✅ Passes | 14 tests, well-audited |
| RunE2EPrecedenceTest | `4f633420` our commit | ✅ Passes | 12 tests (docker group), fixed this session |
| RemoteTestPrecedenceTest | `94dff827` Move tests | ⚠️ Not re-verified | ~3 tests |
| RunActivationPrecedenceTest | `0ee86c56` alias fix | ❌ 3 failures | Asserts `wp_version` but gets null — needs audit: is the test wrong or the code? |
| UtilityPackageValidationTest | `0bd8385a` update unit tests | ❌ 1 failure | JSON-wrapped output breaks string assertion — fix pending |

### TestPackages/ — ⚠️ NOT YET VERIFIED
Large suite covering test package features. Mix of commit origins — some from focused feature work,
some from bulk "WIP" or "Tests tweaks" commits. Need to verify directory by directory.

| Subdir | Files | Last commits | Notes |
|---|---|---|---|
| Caching/ | 3 | `0ebb7121`, `5dcc9644`, `c25c45db WIP` | ComprehensiveCachingTest is WIP |
| Commands/RunE2E/ | 4 | `07efe924`, `ffc75b73`, `0ebb7121` | CI, Config, SUT, Validation |
| CTRF/ | 3 | `07efe924`, `314b00eb`, `c25c45db WIP` | CTRFContractEnforcementTest is WIP |
| Network/ | 1 | `c95fa96f` Tests | |
| Orchestration/ | 3 | `07efe924`, `7c8da3c4`, `c7aaf1de` | |
| Packages/ | 6 | `0ebb7121`, `9d521fc2`, `ccdc5ba3` | Includes subpackage tests |
| ParallelSafetyTest | 1 | `c25c45db WIP` | WIP commit |
| PassthroughArgumentsTest | 2 | `e1883167` | 2 versions of same test |
| Results/Allure/ | 2 | `7c8da3c4`, `be07a63b` | |
| Scenarios/ | 5 | `81d9a454`, `0ee86c56` | User workflow scenarios |
| Security/ | 4 | `c25c45db WIP`, `ccdc5ba3`, `e1883167` | 2 from WIP commit |

### Loose Files — ⚠️ NOT YET VERIFIED

| File | Last commit | Notes |
|---|---|---|
| EnvTest.php | `0ee86c56` alias fix | |
| RunE2ETest.php | `1c7bea22` Make blobs optional | |
| environment/QITEnvUpTest.php | `8382081f` Resolve --woo and --wp | |

### Packages/ — ⚠️ NOT YET VERIFIED

| File | Last commit | Notes |
|---|---|---|
| PackageScaffoldTest.php | `06a3ef8c` updated integration tests | |
| TestPackageWorkflowTest.php | `6aa710dc` Renames config file | |

### Validation/ — ⚠️ NOT YET VERIFIED

| File | Last commit | Notes |
|---|---|---|
| ArtifactValidationTest.php | `c25c45db WIP` | WIP commit — likely broken |

### Deprecated/ — ⚠️ LIKELY DEAD CODE

| File | Last commit | Notes |
|---|---|---|
| BasicTest.php | `906f0f5e WIP` | WIP commit |
| EnvUpThemeTest.php | `906f0f5e WIP` | WIP commit |
| ProfileTest.php | `799cec3f` Remote test config resolver | |
| RunGroupTest.php | `906f0f5e WIP` | WIP commit |

### Known Broken (pre-existing) — ❌ DELETE CANDIDATES

| File | Last commit | Issue |
|---|---|---|
| DirectRequestBuilderTest.php | `f598158a WIP PreCommand refactor` | `Class "QIT_CLI\RequestBuilder" not found` — tries to instantiate CLI classes from integration test process |
| HttpMockingTest.php | `f598158a WIP PreCommand refactor` | Same autoloading issue |
| ImprovedHttpMockingTest.php | `0ee86c56` alias fix | Same autoloading issue |

These try to `new QIT_CLI\RequestBuilder()` but integration tests run CLI as a subprocess.
QIT_CLI classes aren't autoloaded in the test process. Should be deleted or moved to unit tests.

## Test Infrastructure

### Helper Functions (in `bootstrap.php`)
- `qit()` — runs the QIT CLI as a subprocess, returns stdout
- `qit_run_env_up()` — runs `env:up` with `QIT_SELF_TEST=env_up` (bails before Docker)
- `qit_run_e2e()` — runs `run:e2e` with `QIT_SELF_TEST=run_e2e` (runs Docker, bails before test execution)
- `qit_run_remote_test()` — runs remote test commands with `QIT_SELF_TEST=remote_test` (bails before API call)

### Self-Test Modes
- `QIT_SELF_TEST=env_up` — UpEnvironmentCommand bails at step 6 (after config resolution, before Docker)
- `QIT_SELF_TEST=run_e2e` — RunE2ECommand bails after env:up completes (Docker starts, but no test execution)
- `QIT_SELF_TEST=remote_test` — CreateRunCommands bails after options resolution (no API call)

### Snapshot Testing
Tests use `spatie/phpunit-snapshot-assertions`. Snapshots are normalised via `Normalizer::precommand()`
which masks volatile values (paths, env IDs, ports, timestamps, Cloudflare tunnel URLs, plugin versions).

To regenerate a snapshot: delete the `__snapshots__/*.json` file and run the test twice
(first run creates, second run verifies).

### ParaTest Patch
`patches/paratest-stop-on-failure-timeout.patch` fixes two upstream issues:
1. `--stop-on-failure` now actually kills all workers immediately (upstream just clears the queue)
2. 5-minute per-test timeout kills hung workers and reports the test name

Applied automatically via `cweagans/composer-patches` on `composer install`.

## Audit Methodology

When verifying a test directory:
1. Check git history — is this from a "WIP" commit or focused feature work?
2. Read the test — does it test a real user behavior, or is it testing implementation details?
3. Run it — does it pass?
4. If it fails, investigate: is the test wrong, or is the code wrong?
5. Don't "make tests pass at all costs" — fix the right thing.
