# Schema to Test Coverage Mapping

Based on test-package-manifest-schema.json, here's what SHOULD be supported vs what IS tested:

## 1. Package Definition Fields

| Field | Schema Definition | Expected Behavior | Test Coverage | Gap |
|-------|------------------|-------------------|---------------|-----|
| `package` | Required, format `namespace/name` | Package identifier, used for publishing | ✅ All tests use it | - |
| `tags` | Array of strings | Package categorization | ❌ Not tested | No tests verify tag handling |
| `test_type` | Enum: `["e2e"]` | Test type (only e2e supported) | ✅ Default e2e used | - |
| `test_dir` | String, default `./` | Where test files are located | ❌ Not tested | No tests change test_dir |
| `description` | String, max 500 chars | Package description | ⚠️ Present but not validated | No length validation tests |

## 2. Requirements (`requires` object)

| Field | Schema Definition | Expected Behavior | Test Coverage | Gap |
|-------|------------------|-------------------|---------------|-----|
| `requires.plugins` | Object with version constraints | Plugin dependencies with semver | ❌ Not tested | No dependency tests |
| `requires.themes` | Object with version constraints | Theme dependencies with semver | ❌ Not tested | No theme requirement tests |
| `requires.wordpress` | String version constraint | WP version requirement | ⚠️ Indirect (config tests) | No manifest-level WP version tests |
| `requires.php` | String version constraint | PHP version requirement | ⚠️ Indirect (config tests) | No manifest-level PHP version tests |
| `requires.secrets` | Array of env var names | Required secrets validation | ✅ Tests 40-41 | - |
| `requires.external_services` | Array of strings | Documentation only | ❌ Not tested | No external service tests |

## 3. Test Configuration (`test` object)

### 3.1 Phases

| Phase | Schema Definition | Expected Behavior | Test Coverage | Gap |
|-------|------------------|-------------------|---------------|-----|
| `globalSetup` | Lifecycle commands | Runs once before all packages | ✅ Tests 12, 15 | - |
| `setup` | Lifecycle commands | Runs before each package | ✅ Multiple tests | - |
| `run` | Lifecycle commands | Main test execution | ✅ All test packages | - |
| `teardown` | Lifecycle commands | Runs after each package | ✅ Multiple tests | - |
| `globalTeardown` | Lifecycle commands | Runs once after all packages | ✅ Tests 15 | - |

### 3.2 Command Formats

| Format | Schema Definition | Expected Behavior | Test Coverage | Gap |
|--------|------------------|-------------------|---------------|-----|
| String command | Simple string | Auto-detects execution context | ✅ All tests use this | - |
| Object command | Object with properties | Explicit control over execution | ❌ Not tested | No object command tests |
| `command.runs_on` | `"host"` or `"docker"` | Where to execute | ❌ Not tested | No explicit runs_on tests |
| `command.timeout` | Integer 1-3600 | Command timeout | ❌ Not tested | No command-level timeout tests |
| `command.continue_on_error` | Boolean | Continue on failure | ❌ Not tested | No continue_on_error tests |
| `command.env` | Object | Command-specific env vars | ❌ Not tested | No command env tests |

### 3.3 Results

| Field | Schema Definition | Expected Behavior | Test Coverage | Gap |
|-------|------------------|-------------------|---------------|-----|
| `results.ctrf-json` | Required if run phase | CTRF report path | ✅ All test packages | - |
| `results.blob-dir` | Required if run phase | Blob report directory | ✅ Tests 23-24 | - |
| `results.json` | Optional | Original JSON results | ❌ Not tested | No JSON results tests |
| `results.allure-dir` | Optional | Allure report directory | ✅ Tests 32-35 | - |

## 4. Advanced Configuration

| Field | Schema Definition | Expected Behavior | Test Coverage | Gap |
|-------|------------------|-------------------|---------------|-----|
| `mu_plugins` | Array of paths | Must-use plugins to install | ❌ Not tested | No mu_plugins tests |
| `envs` | Object | Package-level env vars | ❌ Not tested | No manifest envs tests (CLI --env tested) |
| `timeout` | Integer 1-3600 | Package-level timeout | ❌ Not tested | No package timeout tests |
| `retry` | Object with times/delay | Retry configuration | ❌ Not tested | No retry mechanism tests |

## 5. Subpackages

| Field | Schema Definition | Expected Behavior | Test Coverage | Gap |
|-------|------------------|-------------------|---------------|-----|
| `subpackages` | Object of package definitions | Derived packages | ✅ Tests 25-28 | Limited coverage |
| Subpackage description | String | Subpackage description | ❌ Not tested | No description tests |
| Subpackage tags | Array | Subpackage categorization | ❌ Not tested | No tag tests |
| Subpackage phase overrides | Object | Override parent phases | ❌ Not tested | No override tests |
| No global phase overrides | Validation rule | Cannot override global phases | ❌ Not tested | No validation tests |

## Summary Statistics

### Coverage by Category:
- **Well Covered (✅)**: 11 features (~26%)
- **Partially Covered (⚠️)**: 3 features (~7%)
- **Not Covered (❌)**: 28 features (~67%)

### Critical Gaps:
1. **Object command format** - Entire feature untested
2. **Package dependencies** (plugins/themes) - No tests
3. **Must-use plugins** - No tests
4. **Retry mechanism** - No tests
5. **Timeout handling** (package and command level) - No tests
6. **Manifest-level env vars** - No tests (only CLI --env tested)
7. **Subpackage inheritance/overrides** - No tests

### Well Tested Areas:
1. **Basic lifecycle phases** - Excellent coverage
2. **Multi-package orchestration** - Excellent coverage
3. **Results/reporting** - Good coverage (CTRF, blob, Allure)
4. **Secret validation** - Good coverage
5. **Basic subpackage execution** - Adequate coverage

## Recommendations for Test Priority

### HIGH Priority (Core functionality gaps):
1. Test object command format with explicit `runs_on`, `timeout`, `continue_on_error`
2. Test plugin/theme dependencies with version constraints
3. Test manifest-level `envs` field
4. Test `retry` mechanism for flaky tests

### MEDIUM Priority (Important features):
5. Test `mu_plugins` installation
6. Test package/command timeout enforcement
7. Test subpackage phase overrides
8. Test `test_dir` configuration

### LOW Priority (Edge cases):
9. Test `tags` field functionality
10. Test `external_services` documentation
11. Test `results.json` field
12. Test description length validation