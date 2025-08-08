# QIT Quick Reference Guide

## Secret Handling

### Declare Required Secrets
```json
// manifest.json
{
  "requires": {
    "secrets": ["API_KEY", "API_SECRET"]
  }
}
```

### Provide Secrets
```bash
# Local
export API_KEY="test_key_123"
export API_SECRET="test_secret_456"
qit run:e2e woocommerce

# CI (GitHub Actions)
env:
  API_KEY: ${{ secrets.API_KEY }}
  API_SECRET: ${{ secrets.API_SECRET }}
```

### What Happens
- ✅ Validates declared secrets exist before running
- ✅ Redacts actual secret values from all output: `key_123` → `[REDACTED:API_KEY]`
- ✅ Securely passes to Docker containers
- ❌ Never stores or transmits secrets
- ❌ No pattern matching - only redacts exact declared values

## Lifecycle Visibility

### All Phases Recorded
```
globalSetup → setup → run → teardown → globalTeardown
     ↓          ↓       ↓        ↓            ↓
  [CTRF]     [CTRF]  [Tests]  [CTRF]      [CTRF]
```

### Automatic CTRF Generation
```
artifacts/
├── ctrf/
│   ├── orchestrator.json  # Lifecycle commands (auto-generated)
│   └── package-1.json     # Test results
└── final/
    └── ctrf-report.json   # Merged report
```

### Output Control

| Environment | Default | `-v` | `-vv` | On Failure |
|------------|---------|------|-------|------------|
| **Local** | Commands only | + Output | + Debug | Redacted output |
| **CI** | Commands only | + Output | + Debug | Redacted output |

```bash
# Quiet
qit run:e2e woocommerce -q

# Verbose (show output)
qit run:e2e woocommerce -v

# Very verbose (debug)
qit run:e2e woocommerce -vv

# Force suppression
QIT_SUPPRESS_OUTPUT=true qit run:e2e woocommerce

# Force display (even in CI)
QIT_SUPPRESS_OUTPUT=false qit run:e2e woocommerce
```

## Package Types

### Test Package (with run phase)
```json
{
  "test": {
    "phases": {
      "setup": ["npm install"],
      "run": ["npm test"],        // ← Required
      "teardown": ["./cleanup.sh"]
    },
    "results": {                  // ← Required with run
      "ctrf-json": "./results.json",
      "blob-dir": "./blob-report"
    }
  }
}
```

### Utility Package (no run phase)
```json
{
  "test": {
    "phases": {
      "setup": ["./seed-data.sh"],
      "teardown": ["./cleanup.sh"]
      // No run phase - no results needed
    }
  }
}
```

## Common Patterns

### Payment Gateway Testing
```json
{
  "requires": {
    "secrets": [
      "STRIPE_TEST_KEY",
      "PAYPAL_SANDBOX_ID",
      "PAYPAL_SANDBOX_SECRET"
    ]
  }
}
```

### API Testing
```bash
# Validate in setup
if [[ ! "$API_KEY" =~ ^test_ ]]; then
  echo "Error: Use test API key only"
  exit 1
fi
```

### CI Configuration
```yaml
# GitHub Actions
env:
  CI: true  # Enables smart output suppression
  STRIPE_KEY: ${{ secrets.STRIPE_KEY }}

# GitLab
variables:
  CI: "true"
  STRIPE_KEY: $STRIPE_KEY
```

## Security Checklist

- [ ] Never commit secrets to git
- [ ] Use test/sandbox credentials only
- [ ] Add `.env` files to `.gitignore`
- [ ] Document how to obtain secrets
- [ ] Validate secret format in scripts
- [ ] Review package code before running

## Debugging

### Check Secrets
```bash
# Are they set?
env | grep -E "API_KEY|SECRET" | sed 's/=.*/=***/'

# Debug in CI (remove after!)
echo "KEY exists: $([[ -n "$API_KEY" ]] && echo yes || echo no)"
```

### View Lifecycle Results
```bash
# Check orchestrator CTRF
cat artifacts/*/ctrf/orchestrator.json | jq '.results.tests[] | {name, status, duration}'

# Find failures
cat artifacts/*/ctrf/orchestrator.json | jq '.results.tests[] | select(.status=="failed")'

# See output
cat artifacts/*/ctrf/orchestrator.json | jq '.results.tests[].extra.output'
```

### Common Issues

| Problem | Solution |
|---------|----------|
| "Missing required secrets" | `export SECRET_NAME="value"` |
| Secrets visible in logs | Report bug - pattern not caught |
| No lifecycle visibility | Check `orchestrator.json` exists |
| Output suppressed | Add `-v` flag or check failures |

## Command Reference

```bash
# Run with secrets
export API_KEY="test_123"
qit run:e2e woocommerce

# Verbose output
qit run:e2e woocommerce -v

# Debug mode
QIT_SUPPRESS_OUTPUT=false CI=false qit run:e2e woocommerce -vv

# View report
qit report

# Check last artifacts
ls -la /tmp/qit-e2e-artifacts-*/
```

## Links

- [Full Secret Documentation](./SECRET_HANDLING.md)
- [Lifecycle Visibility Details](./LIFECYCLE_VISIBILITY.md)
- [CI/CD Examples](./SECRET_HANDLING.md#cicd-integration)
- [Troubleshooting](./SECRET_HANDLING.md#troubleshooting)