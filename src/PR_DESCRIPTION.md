# Improve Parameter Aliasing and Activation Test Handling

## Summary

This PR refines activation test handling and improves the parameter aliasing implementation from PR #365.

The work was driven by self-test snapshot regressions that revealed issues with parameter handling and activation test behavior. The changes ensure snapshot tests pass consistently without regressions.

## Changes

### Activation Test Improvements

#### 1. **Prevent Double Activation of SUT**
- Added `--skip_activating_plugins` and `--skip_activating_themes` options to `env:up` command
- Activation tests now skip pre-activating the SUT during environment setup
- SUT is activated only once during actual test execution
- Prevents potential issues from plugins/themes being activated twice

#### 2. **Improved Theme Dependency Handling**
- Parent themes (like Storefront) are now **always** installed when child themes are present
- Works correctly even when `skip_activating_themes` is true
- Separated parent theme installation from theme activation logic
- Ensures child themes (like Bistro) have their dependencies available

#### 3. **Property Access for Activation Flags**
- Updated references to `skip_activating_plugins` and `skip_activating_themes`
- Now reads from `$env_info` instead of class properties
- Added missing properties to `QITEnvInfo` class

#### 4. **Fixed `is_development` Flag Detection**
- Fixed detection for activation test type based on test packages
- Determines development builds by checking if SUT is from a recognized marketplace
- Checks if extension source is `wporg` or `wccom`
- Provides accurate identification of marketplace vs development extensions

#### 5. **Fixed Missing Test Summary**
- Test summary is now properly included in test results
- Ensures complete test reporting for activation tests

#### 6. **Enhanced Option Handling**
- `QITInput::getOption()` now includes programmatically set values, not just CLI-provided options
- Allows internal code to set options that are properly recognized

### Parameter Aliasing Refinement

Building on PR #365, this implementation refines the approach with uni-directional normalization to prevent empty string issues and ensure consistent behavior:
- **CLI**: Normalizes argv before Symfony Console parses it (`--php=8.0` → `--php_version=8.0`)
- **Manager**: Centralized alias handling treats both forms as true aliases with identical behavior
- **Result**: No empty string bugs, consistent behavior, fail-fast conflict detection

**Available aliases:**
- `--php` ↔ `--php_version`
- `--wp` ↔ `--wordpress_version`
- `--woo` ↔ `--woocommerce_version`

**Example usage:**
```bash
# Short form (convenient)
qit run:activation woocommerce --php=8.0 --wp=stable --woo=stable

# Long form (still works)
qit run:activation woocommerce --php_version=8.0 --wordpress_version=stable --woocommerce_version=stable

# Mixed forms (works perfectly)
qit run:activation woocommerce --php=8.0 --wordpress_version=stable --woo=stable

# Conflict detection (fails fast)
qit run:activation woocommerce --php=8.0 --php_version=7.4
# Error: Cannot specify both "php" and "php_version". Use one or the other.
```

## Testing

- ✅ **qit-cli**: All tests pass (79 tests, 271 assertions)
- ✅ **qit-manager**: All tests pass (591 tests, 2632 assertions)
- ✅ **Parameter aliasing**: Comprehensive testing (22 critical path test cases)
- ✅ **Static analysis**: PHPCS, PHPStan, Phan all passing

## Files Changed

**qit-cli:**
- `src/qit-cli.php` - Argv normalization with conflict detection
- `src/src/Commands/Environment/UpEnvironmentCommand.php` - Skip activation options
- `src/src/Environment/Environments/QITEnvironment.php` - Fixed property access, theme handling
- `src/src/Environment/Environments/ThemeActivation.php` - Parent theme installation logic
- `src/src/Environment/Environments/QITEnvInfo.php` - Added missing properties
- `src/src/Commands/RunE2ECommand.php` - Fixed `is_development` detection
- `src/src/QITInput.php` - Enhanced option handling
- `src/src/Utils/LocalTestRunNotifier.php` - Documentation fix

**qit-manager:**
- `plugins/cd-manager/src/REST/V1/Endpoints/Tests/TestRunEndpoint.php` - Centralized parameter aliasing
- `plugins/cd-manager/tests/CLI/CLISyncTest.php` - Updated snapshot normalization
- Test snapshots updated for new schema

## Breaking Changes

None - fully backward compatible. Long-form parameters continue to work as before.

## Impact

### Improvements
- ✅ Activation tests run cleanly with SUT activated only once (prevents double activation)
- ✅ Parent themes automatically installed for child themes (e.g., Bistro/Storefront)
- ✅ Accurate development build detection for marketplace extensions
- ✅ Parameter aliases work consistently regardless of which form is used
- ✅ Improved empty string handling prevents version mismatches
- ✅ Shorter, more convenient parameter names available
