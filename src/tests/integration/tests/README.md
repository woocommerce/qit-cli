# QIT CLI Integration Tests Organization

This directory contains the integration tests for the QIT CLI, organized by functionality and concern.

## Directory Structure

### `Caching/`
Tests for various caching mechanisms:
- **ChecksumCachingTest** - Checksum-based validation for rolling versions
- **ComprehensiveCachingTest** - Full caching scenarios and edge cases
- **TestPackageCachingTest** - Package download and storage caching

### `Commands/`
Tests for CLI commands and their behaviors:
- **RunE2E/** - Tests specific to the `run:e2e` command
  - **Configuration/** - Configuration file handling and precedence
  - **Validation/** - Input validation and error handling
- **ExtensionResolutionTest** - Extension slug resolution
- **RunE2ECommandTest** - Core E2E command functionality
- **UpEnvironmentCommandTest** - Environment setup command

### `CTRF/`
Common Test Results Format tests:
- **CTRFContractEnforcementTest** - CTRF validation, merging, and contract enforcement

### `Deprecated/`
Legacy tests that should be migrated or removed:
- Tests using old patterns or deprecated functionality
- Contains snapshots for backward compatibility

### `Fixtures/`
Mixed integration tests (being reorganized):
- Contains test packages and helpers used by other tests
- Gradually being migrated to more specific directories

### `Helpers/`
Utility classes for testing:
- **CTRFHelper** - Helper functions for CTRF manipulation

### `Orchestration/`
Test execution orchestration:
- **ExecutionOrderTest** - Package execution order, lifecycle phases, and orchestration

### `Packages/`
Package management tests:
- **Registry/** - Package publish, delete, and registry operations
- **Subpackages/** - Subpackage functionality and collision prevention
- **PackageScaffoldTest** - Package scaffolding functionality
- **TestPackageWorkflowTest** - Package workflow and lifecycle

### `PreCommand/`
Tests for command precedence and configuration merging:
- **EnvUpPrecedenceTest** - Environment setup precedence rules
- **RemoteTestPrecedenceTest** - Remote test configuration precedence
- **RunActivationPrecedenceTest** - Activation command precedence
- **RunE2EPrecedenceTest** - E2E run command precedence

### `Results/`
Test result handling and reporting:
- **Allure/** - Allure report generation and upload
  - **AllureUploadTest** - Upload behavior when tests pass
  - **AllureFailureUploadTest** - Upload behavior on failures

### `Security/`
Security-related tests:
- **SecretValidationTest** - Secret requirement validation
- **OutputRedactionTest** - Secret redaction in output

### `Traits/`
Reusable test traits:
- **CtrfSnapshotNormalizer** - CTRF normalization for snapshots
- **ScaffoldHelpers** - Helper methods for scaffolding tests
- **SnapshotHelpers** - Snapshot testing utilities

### `Utils/`
Testing utilities:
- **Normalizer** - Data normalization for testing

### `environment/`
Environment-specific tests:
- **QITEnvUpTest** - QIT environment setup tests

## Test Naming Conventions

- Test classes should end with `Test`
- Test methods should start with `test_`
- Use descriptive names that explain what is being tested
- Group related tests in the same class

## Running Tests

From the integration tests directory:
```bash
# Run all tests
./vendor/bin/phpunit

# Run tests in a specific directory
./vendor/bin/phpunit tests/Security/

# Run a specific test file
./vendor/bin/phpunit tests/CTRF/CTRFContractEnforcementTest.php

# Run with testdox for readable output
./vendor/bin/phpunit --testdox tests/Security/
```

## Adding New Tests

1. Identify the appropriate directory based on what you're testing
2. If no suitable directory exists, create one following the existing pattern
3. Use existing helper classes and traits where appropriate
4. Follow the namespace convention: `QIT\IntegrationTests\{Directory}\{TestClass}`
5. Clean up test data in `tearDown()` method

## Important Notes

- Tests should be independent and not rely on execution order
- Use `TestCleanupHelper` to clean up test packages
- Let the OS handle temp directory cleanup
- Avoid asserting on output when possible (outputs can lie)
- Prefer behavioral testing over implementation testing