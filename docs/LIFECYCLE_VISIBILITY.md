# Lifecycle Visibility in Test Reports

QIT automatically generates CTRF (Common Test Results Format) entries for all lifecycle phases, providing complete visibility into test package execution.

## Overview

Every command executed during lifecycle phases (`globalSetup`, `setup`, `teardown`, `globalTeardown`) is automatically recorded and included in test reports. This provides:

- **Complete execution timeline** - See what ran and when
- **Performance insights** - Identify slow setup/teardown
- **Debugging information** - Understand failures in lifecycle phases
- **Zero configuration** - Works automatically for all packages

## How It Works

### Automatic Recording

When a test package runs, QIT records each lifecycle command:

```json
{
  "test": {
    "phases": {
      "setup": [
        "npm install",
        "wp plugin activate woocommerce"
      ],
      "run": ["npm test"],
      "teardown": ["./cleanup.sh"]
    }
  }
}
```

Results in CTRF entries like:

```json
{
  "name": "[setup] npm install",
  "status": "passed",
  "duration": 15234,
  "extra": {
    "type": "lifecycle",
    "phase": "setup",
    "package": "my-test-package",
    "exitCode": 0,
    "output": "Installing dependencies...\nAdded 523 packages"
  }
}
```

### Report Integration

Lifecycle results appear alongside test results in reports:

```
TEST RESULTS SUMMARY
═══════════════════════════════════════════════════════════════
Status:        ✓ PASSED
Packages:      2/2 executed
Tests:         45 passed, 0 failed, 0 skipped
Lifecycle:     8 commands executed (all passed)
Duration:      2m 34s
```

## Lifecycle Phases

### globalSetup
- Runs once before all packages
- Used for environment-wide initialization
- Example: Installing WordPress, activating plugins

### setup
- Runs before each package's tests
- Package-specific initialization
- Example: Seeding test data, configuring settings

### run
- Actual test execution
- Generates standard test CTRF (not lifecycle CTRF)
- Example: Playwright tests, PHPUnit tests

### teardown
- Runs after each package's tests
- Package-specific cleanup
- Example: Removing test data, resetting state

### globalTeardown
- Runs once after all packages
- Environment-wide cleanup
- Example: Exporting logs, final cleanup

## Output Management

### Default Behavior

Output behavior depends on environment and verbosity:

| Environment | Default | With `-v` | On Failure |
|------------|---------|-----------|------------|
| Local | Show commands | Show output | Show output |
| CI (`CI=true`) | Hide output | Show output | Show redacted output |

### Examples

#### Local Development (Default)
```bash
┌─ PACKAGE [1/2]: payment-tests ────────────────────────────
│ Type: Local Package
├────────────────────────────────────────────────────────────
│ [host] npm install
│ [host] ./configure-stripe.sh
│ [docker] wp option update stripe_mode test
└────────────────────────────────────────────────────────────
```

#### CI Environment (Default)
```bash
┌─ PACKAGE [1/2]: payment-tests ────────────────────────────
│ Type: Local Package
├────────────────────────────────────────────────────────────
│ [host] npm install
│ [host] ./configure-stripe.sh
│ [docker] wp option update stripe_mode test
└────────────────────────────────────────────────────────────
```
(Output suppressed, only commands shown)

#### On Failure (Any Environment)
```bash
│ [host] ./configure-stripe.sh
│   Configuring Stripe...
│   Error: API key validation failed
│   [Showing last 50 lines of suppressed output]
│   Using key: [REDACTED:STRIPE_KEY]
│   Response: 401 Unauthorized
```

## CTRF Structure

### Lifecycle Entry Format

Each lifecycle command generates a CTRF entry:

```json
{
  "name": "[phase] command",
  "id": "package-phase-index",
  "status": "passed" | "failed",
  "duration": 1234,  // milliseconds
  "extra": {
    "type": "lifecycle",
    "phase": "setup" | "teardown" | "globalSetup" | "globalTeardown",
    "package": "package-identifier",
    "exitCode": 0,
    "output": "First 1KB of output (truncated)..."
  }
}
```

### Merged Report

The orchestrator CTRF (`orchestrator.json`) is automatically merged with test CTRFs:

```
artifacts/
├── ctrf/
│   ├── orchestrator.json        # Lifecycle commands
│   ├── package-1.json           # Test results
│   └── package-2.json           # Test results
└── final/
    └── ctrf/
        └── ctrf-report.json     # Merged report
```

## Filtering and Analysis

### Identifying Lifecycle Entries

Lifecycle entries have `extra.type = "lifecycle"`:

```javascript
// Filter lifecycle entries from CTRF
const lifecycleEntries = ctrfReport.results.tests.filter(
  test => test.extra?.type === 'lifecycle'
);

// Group by phase
const byPhase = lifecycleEntries.reduce((acc, entry) => {
  const phase = entry.extra.phase;
  acc[phase] = acc[phase] || [];
  acc[phase].push(entry);
  return acc;
}, {});
```

### Performance Analysis

Find slow lifecycle commands:

```javascript
const slowCommands = lifecycleEntries
  .filter(entry => entry.duration > 5000) // > 5 seconds
  .sort((a, b) => b.duration - a.duration);

console.log('Slowest lifecycle commands:');
slowCommands.forEach(cmd => {
  console.log(`${cmd.name}: ${cmd.duration}ms`);
});
```

### Failure Investigation

Get failed lifecycle commands:

```javascript
const failures = lifecycleEntries.filter(
  entry => entry.status === 'failed'
);

failures.forEach(failure => {
  console.log(`Failed: ${failure.name}`);
  console.log(`Exit code: ${failure.extra.exitCode}`);
  console.log(`Output: ${failure.extra.output}`);
});
```

## Configuration

### Output Verbosity

Control output detail level:

```bash
# Minimal output
qit run:e2e woocommerce -q

# Default - show commands
qit run:e2e woocommerce

# Verbose - show output
qit run:e2e woocommerce -v

# Very verbose - show everything
qit run:e2e woocommerce -vv
```

### Environment Variables

```bash
# Force suppression
QIT_SUPPRESS_OUTPUT=true qit run:e2e woocommerce

# Force display
QIT_SUPPRESS_OUTPUT=false qit run:e2e woocommerce

# Override CI detection
CI=false qit run:e2e woocommerce -v
```

## Use Cases

### 1. Performance Optimization

Identify slow setup:

```json
{
  "name": "[setup] npm install",
  "duration": 45000,  // 45 seconds - needs optimization
  "extra": {
    "output": "Installing 1500 packages..."
  }
}
```

Solution: Cache dependencies or use lighter setup.

### 2. Debugging Failures

Understand why setup failed:

```json
{
  "name": "[setup] wp plugin activate my-plugin",
  "status": "failed",
  "extra": {
    "exitCode": 1,
    "output": "Error: Plugin file not found"
  }
}
```

### 3. Audit Trail

Complete record of what ran:

```json
[
  {"name": "[globalSetup] wp core install", "duration": 3000},
  {"name": "[setup] ./import-products.sh", "duration": 5000},
  {"name": "[setup] wp user create test", "duration": 500},
  {"name": "[teardown] ./cleanup.sh", "duration": 1000},
  {"name": "[globalTeardown] wp db export", "duration": 2000}
]
```

### 4. Resource Usage Tracking

Monitor command execution patterns:

- Which commands run most frequently
- Which commands take the longest
- Which commands fail most often

## Best Practices

### 1. Keep Lifecycle Commands Fast

```json
{
  "phases": {
    "setup": [
      "wp option update --skip-themes --skip-plugins",  // Fast
      "wp rewrite flush --hard"  // Avoid - slow
    ]
  }
}
```

### 2. Make Output Meaningful

```bash
# Good - informative output
echo "Importing 50 test products..."
wp import products.xml
echo "✓ Products imported successfully"

# Bad - no context
wp import products.xml
```

### 3. Handle Failures Gracefully

```bash
#!/bin/bash
set -e  # Exit on error

echo "Setting up Stripe test mode..."
if ! wp option update stripe_mode test; then
    echo "Failed to set Stripe mode"
    exit 1
fi
echo "✓ Stripe configured"
```

### 4. Use Appropriate Phases

- **globalSetup**: Environment-wide, runs once
- **setup**: Package-specific, runs per package
- **teardown**: Cleanup after tests
- **globalTeardown**: Final cleanup

### 5. Consider Utility Packages

For shared setup without tests:

```json
{
  "package": "test-data-seeder",
  "test": {
    "phases": {
      "setup": ["./seed-test-data.sh"],
      "teardown": ["./cleanup-test-data.sh"]
    }
    // No "run" phase - utility package
  }
}
```

## Troubleshooting

### Lifecycle Commands Not Appearing

**Problem:** Lifecycle commands don't show in reports

**Solution:**
- Check `orchestrator.json` exists in artifacts
- Verify CTRF merge completes successfully
- Ensure commands actually execute (not skipped)

### Output Truncated

**Problem:** Need to see more output

**Solution:**
```bash
# Increase verbosity
qit run:e2e woocommerce -vv

# Check artifacts directory for full logs
cat artifacts/*/orchestrator.json | jq '.results.tests[].extra.output'
```

### Performance Issues

**Problem:** Lifecycle phases taking too long

**Solution:**
1. Identify slow commands in CTRF
2. Optimize or parallelize where possible
3. Cache dependencies
4. Use minimal fixtures

## Related Documentation

- [Secret Handling](./SECRET_HANDLING.md) - How secrets are managed
- [Test Package Manifest](./MANIFEST_SCHEMA.md) - Package configuration
- [CTRF Format](./CTRF_FORMAT.md) - Report structure
- [Output Control](./OUTPUT_CONTROL.md) - Verbosity settings