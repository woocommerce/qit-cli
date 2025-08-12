# Coverage Gap Tests

This directory contains new tests added to improve coverage of untested features identified in the schema-to-test-mapping.md analysis.

## Running These Tests

### Run ONLY the new coverage gap tests:
```bash
cd /home/lucas/automattic/qit/qit-cli/src/tests/integration
./vendor/bin/phpunit --group coverage-gaps
```

### Run a specific subset of coverage gap tests:
```bash
# Object command format tests
./vendor/bin/phpunit --group command-format

# Dependency tests
./vendor/bin/phpunit --group dependencies

# Configuration tests  
./vendor/bin/phpunit --group config-fields

# Timeout/retry tests
./vendor/bin/phpunit --group resilience
```

### Run everything EXCEPT coverage gap tests (original 43 tests):
```bash
./vendor/bin/phpunit --exclude-group coverage-gaps
```

## Test Organization

Each test class should include:
- `@group coverage-gaps` - Always include this for new tests
- Additional specific groups for categorization
- Clear documentation of what gap is being addressed

## Test Files

1. **CommandFormatTest.php** - Tests for object command format (@group command-format)
2. **DependencyValidationTest.php** - Tests for plugin/theme requirements (@group dependencies)
3. **ManifestConfigurationTest.php** - Tests for envs, timeout, retry fields (@group config-fields)
4. **MustUsePluginsTest.php** - Tests for mu_plugins field (@group mu-plugins)
5. **SubpackageInheritanceTest.php** - Tests for subpackage overrides (@group subpackages)

## Priority Order

Based on schema-to-test-mapping.md analysis:

### HIGH Priority
- [x] Object command format with runs_on, timeout, continue_on_error
- [ ] Plugin/theme dependencies with version constraints
- [ ] Manifest-level envs field
- [ ] Retry mechanism for flaky tests

### MEDIUM Priority
- [ ] Must-use plugins installation
- [ ] Package/command timeout enforcement
- [ ] Subpackage phase overrides
- [ ] test_dir configuration

### LOW Priority
- [ ] Tags field functionality
- [ ] External services documentation
- [ ] results.json field
- [ ] Description length validation