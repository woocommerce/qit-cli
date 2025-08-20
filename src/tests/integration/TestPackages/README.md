# TestPackages Integration Tests

This directory contains the reorganized integration tests that were previously in the `tests/Fixtures/` directory. These tests focus on test package execution, orchestration, and result handling.

## Directory Structure

### `Caching/`
Tests for various caching mechanisms:
- **ChecksumCachingTest** - Checksum-based validation for rolling versions
- **ComprehensiveCachingTest** - Full caching scenarios and edge cases
- **TestPackageCachingTest** - Package download and storage caching

### `Commands/RunE2E/`
Tests for the `run:e2e` command behaviors:
- **CIBehaviorTest** - CI-specific output behavior
- **Configuration/** - Configuration file handling and precedence
- **SUTHandlingTest** - System Under Test (plugin/theme) handling
- **Validation/** - Input validation and error handling

### `CTRF/`
Common Test Results Format tests:
- **CTRFContractEnforcementTest** - CTRF validation and contract enforcement
- **IsLocalFieldTest** - Local package field in CTRF
- **OrchestratorCTRFTest** - Orchestrator CTRF generation for lifecycle phases

### `Orchestration/`
Test execution orchestration:
- **ExecutionOrderTest** - Package execution order and lifecycle phases
- **IntegrationTest** - High-level orchestration guarantees
- **StateManagementTest** - State sharing and isolation between packages

### `Packages/`
Package management tests:
- **CommandFormatTest** - Object command format in manifests
- **Registry/** - Package publish and delete operations
- **Subpackages/** - Subpackage functionality
  - **ParentSubpackageCombinationTest** - Parent + subpackage execution
  - **SubpackageCollisionTest** - Collision prevention
  - **SubpackageDeletionTest** - Deletion cascading
  - **SubpackageExecutionTest** - Core subpackage functionality (20 tests)
  - **SubpackageTestHelper** - Helper utilities

### `Results/Allure/`
Allure report generation and upload:
- **AllureUploadTest** - Upload behavior when tests pass
- **AllureFailureUploadTest** - Upload behavior on failures

### `Security/`
Security-related tests:
- **SecretValidationTest** - Secret requirement validation
- **OutputRedactionTest** - Secret redaction in output

## Running Tests

From the integration directory:
```bash
# Run all TestPackages tests
./vendor/bin/phpunit TestPackages/

# Run tests in a specific directory
./vendor/bin/phpunit TestPackages/Security/

# Run a specific test file
./vendor/bin/phpunit TestPackages/CTRF/CTRFContractEnforcementTest.php

# Run with testdox for readable output
./vendor/bin/phpunit --testdox TestPackages/Security/
```

## Namespace Convention

All tests in this directory use the namespace: `QIT\IntegrationTests\TestPackages\{Directory}\{TestClass}`

For example:
- `QIT\IntegrationTests\TestPackages\Security\SecretValidationTest`
- `QIT\IntegrationTests\TestPackages\Packages\Subpackages\SubpackageExecutionTest`

## Test Organization History

These tests were reorganized from a single `Fixtures/` directory that contained 20+ mixed-concern test files. The reorganization:
- Grouped tests by functionality rather than implementation
- Removed duplicate tests
- Deleted non-functional tests (skipped/incomplete)
- Created clear separation of concerns
- Improved test discoverability and maintainability