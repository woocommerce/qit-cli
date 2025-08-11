# Secret Handling in QIT

This document describes how QIT handles secrets and sensitive information in test packages.

## Table of Contents
- [Overview](#overview)
- [Declaring Required Secrets](#declaring-required-secrets)
- [Providing Secrets](#providing-secrets)
- [Security Features](#security-features)
- [CI/CD Integration](#cicd-integration)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

## Overview

QIT provides secure secret management for test packages that need to interact with external services (e.g., payment gateways, APIs). The system ensures that:

- **Secrets are never stored** by QIT
- **Secrets are validated** before tests run
- **Secrets are redacted** from all output
- **Secrets are safely injected** into test environments

## Declaring Required Secrets

Test packages declare required secrets in their `qit-package.json`:

```json
{
  "package": "stripe-integration-tests",
  "requires": {
    "secrets": [
      "STRIPE_SECRET_KEY",
      "STRIPE_WEBHOOK_SECRET"
    ]
  },
  "test": {
    "phases": {
      "setup": ["./setup-stripe.sh"],
      "run": ["npm test"]
    }
  }
}
```

When this package runs, QIT will:
1. Check that `STRIPE_SECRET_KEY` and `STRIPE_WEBHOOK_SECRET` are set
2. Fail fast with helpful instructions if they're missing
3. Automatically redact these values from all output

## Providing Secrets

### Local Development

Set environment variables before running tests:

```bash
# Option 1: Export in shell
export STRIPE_SECRET_KEY="sk_test_..."
export STRIPE_WEBHOOK_SECRET="whsec_..."
qit run:e2e woocommerce --config=qit.json

# Option 2: Inline (less secure, visible in shell history)
STRIPE_SECRET_KEY="sk_test_..." STRIPE_WEBHOOK_SECRET="whsec_..." qit run:e2e woocommerce
```

### Using .env Files

Create a `.env.test` file (remember to add to `.gitignore`!):

```bash
# .env.test
STRIPE_SECRET_KEY=sk_test_EXAMPLE_KEY_DO_NOT_USE_IN_PRODUCTION
STRIPE_WEBHOOK_SECRET=whsec_EXAMPLE_SECRET_1234567890
API_TOKEN=token_EXAMPLE_xyz789
```

Then source it before running:

```bash
source .env.test
qit run:e2e woocommerce --config=qit.json
```

### CI/CD Environments

See [CI/CD Integration](#cicd-integration) section below.

## Security Features

### 1. Automatic Redaction

QIT automatically redacts the actual values of declared secrets from all output:

```bash
# Your script outputs:
echo "Connecting with key: $STRIPE_SECRET_KEY"

# QIT displays:
Connecting with key: [REDACTED:STRIPE_SECRET_KEY]
```

### 2. Declaration-Based Redaction

QIT only redacts the actual values of secrets that are explicitly declared in test package manifests:

- **No pattern matching** - Only exact values are redacted
- **No false positives** - Won't accidentally redact similar-looking text
- **Clear traceability** - Know exactly which secret was redacted
- **URL/Base64 encoding** - Also redacts encoded versions of secret values

### 3. Docker Security

Secrets are passed to Docker containers using `--env NAME` (not `--env NAME=value`), preventing them from appearing in process lists:

```bash
# Secure (QIT does this automatically)
docker exec --env STRIPE_SECRET_KEY container_name command

# Insecure (QIT prevents this)
docker exec -e STRIPE_SECRET_KEY=sk_test_123 container_name command
```

### 4. Output Suppression

In CI environments (`CI=true`), lifecycle output is suppressed by default:

- **Local**: Output shown (with redaction)
- **CI**: Output hidden unless command fails
- **Failure**: Redacted output shown for debugging

## CI/CD Integration

### GitHub Actions

```yaml
name: E2E Tests
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: Install QIT
        run: |
          curl -sSL https://qit.woo.com/install.sh | bash
          
      - name: Run E2E Tests
        env:
          # Secrets stored in GitHub Settings > Secrets
          STRIPE_SECRET_KEY: ${{ secrets.STRIPE_SECRET_KEY }}
          STRIPE_WEBHOOK_SECRET: ${{ secrets.STRIPE_WEBHOOK_SECRET }}
          CI: true  # Enables output suppression
        run: |
          qit run:e2e woocommerce --config=qit.json
```

### GitLab CI

```yaml
e2e-tests:
  image: php:8.2
  variables:
    CI: "true"
    # Secrets from GitLab Settings > CI/CD > Variables
    STRIPE_SECRET_KEY: $STRIPE_SECRET_KEY
    STRIPE_WEBHOOK_SECRET: $STRIPE_WEBHOOK_SECRET
  script:
    - curl -sSL https://qit.woo.com/install.sh | bash
    - qit run:e2e woocommerce --config=qit.json
```

### CircleCI

```yaml
version: 2.1
jobs:
  e2e-tests:
    docker:
      - image: cimg/php:8.2
    environment:
      CI: true
    steps:
      - checkout
      - run:
          name: Install QIT
          command: curl -sSL https://qit.woo.com/install.sh | bash
      - run:
          name: Run E2E Tests
          command: qit run:e2e woocommerce --config=qit.json
          environment:
            # Secrets from Project Settings > Environment Variables
            STRIPE_SECRET_KEY: $STRIPE_SECRET_KEY
            STRIPE_WEBHOOK_SECRET: $STRIPE_WEBHOOK_SECRET
```

### Jenkins

```groovy
pipeline {
    agent any
    environment {
        CI = 'true'
        // Secrets from Jenkins Credentials
        STRIPE_SECRET_KEY = credentials('stripe-secret-key')
        STRIPE_WEBHOOK_SECRET = credentials('stripe-webhook-secret')
    }
    stages {
        stage('Test') {
            steps {
                sh 'curl -sSL https://qit.woo.com/install.sh | bash'
                sh 'qit run:e2e woocommerce --config=qit.json'
            }
        }
    }
}
```

## Best Practices

### 1. Never Commit Secrets

```bash
# .gitignore
.env
.env.*
*.key
*.pem
secrets/
```

### 2. Use Descriptive Secret Names

```json
{
  "requires": {
    "secrets": [
      "STRIPE_TEST_SECRET_KEY",    // Good: Clear it's for testing
      "PAYPAL_SANDBOX_CLIENT_ID",  // Good: Indicates sandbox
      "API_KEY"                     // Bad: Too generic
    ]
  }
}
```

### 3. Document Secret Requirements

In your test package README:

```markdown
## Required Secrets

This test package requires the following secrets:

- `STRIPE_TEST_SECRET_KEY`: Stripe test mode secret key
  - Get from: https://dashboard.stripe.com/test/apikeys
  - Format: `sk_test_...`
  
- `STRIPE_WEBHOOK_SECRET`: Stripe webhook endpoint secret
  - Get from: https://dashboard.stripe.com/test/webhooks
  - Format: `whsec_...`
```

### 4. Validate Secret Format

In your test scripts, validate secret format early:

```bash
#!/bin/bash
# setup.sh

if [[ ! "$STRIPE_SECRET_KEY" =~ ^sk_test_ ]]; then
    echo "Error: STRIPE_SECRET_KEY should start with 'sk_test_'"
    exit 1
fi
```

### 5. Use Test/Sandbox Credentials Only

Never use production credentials in test packages:

```json
{
  "description": "Tests Stripe integration using TEST MODE credentials only",
  "requires": {
    "secrets": ["STRIPE_TEST_SECRET_KEY"]  // Clear it's for testing
  }
}
```

## Troubleshooting

### Missing Secrets Error

**Problem:**
```
Missing required secrets:
  • STRIPE_SECRET_KEY
  • STRIPE_WEBHOOK_SECRET
```

**Solution:**
1. Check spelling of environment variable names
2. Ensure variables are exported: `export STRIPE_SECRET_KEY="..."`
3. Verify with: `echo $STRIPE_SECRET_KEY`

### Secrets Appearing in Output

**Problem:** Secret values visible in logs

**Possible Causes:**
1. Script echoing to stderr (QIT only redacts stdout by default)
2. Secret embedded in error messages
3. URL-encoded version of secret

**Solution:**
- Avoid echoing secrets directly
- Use QIT's redaction for both stdout and stderr
- Report issues if patterns aren't caught

### Docker Permission Issues

**Problem:** Secrets not available in Docker container

**Solution:**
- Ensure secrets are exported in host environment
- Check Docker has permission to access environment
- Verify using: `docker exec container_name printenv SECRET_NAME`

### CI Secrets Not Working

**Problem:** Tests fail in CI but work locally

**Common Issues:**
1. **Secret not set in CI**: Check CI platform's secret configuration
2. **Name mismatch**: CI secret name doesn't match manifest
3. **Escaping issues**: Special characters not properly escaped

**Debugging:**
```yaml
# Add debug step (remove after fixing!)
- name: Debug (REMOVE AFTER TESTING)
  run: |
    echo "STRIPE_SECRET_KEY is set: $([[ -n "$STRIPE_SECRET_KEY" ]] && echo 'yes' || echo 'no')"
    echo "Length: ${#STRIPE_SECRET_KEY}"
```

## Output Control

### Verbosity Levels

Control how much output you see:

```bash
# Quiet mode - minimal output
qit run:e2e woocommerce -q

# Normal mode - default
qit run:e2e woocommerce

# Verbose mode - show lifecycle output (redacted)
qit run:e2e woocommerce -v

# Very verbose - show all output (redacted)
qit run:e2e woocommerce -vv
```

### Environment Variables

Fine-tune output behavior:

```bash
# Force output suppression
QIT_SUPPRESS_OUTPUT=true qit run:e2e woocommerce

# Force output display (even in CI)
QIT_SUPPRESS_OUTPUT=false qit run:e2e woocommerce

# Disable in CI for debugging
CI=false qit run:e2e woocommerce -v
```

## Security Considerations

### What QIT Does

- ✅ Validates declared secrets exist before running
- ✅ Redacts actual values of declared secrets from all output
- ✅ Passes secrets securely to Docker
- ✅ Prevents secrets in process lists
- ✅ Truncates output in CTRF reports

### What QIT Does NOT Do

- ❌ Store or persist secrets
- ❌ Transmit secrets to QIT servers
- ❌ Share secrets between test runs
- ❌ Provide secret values
- ❌ Manage secret rotation

### Your Responsibilities

1. **Obtain appropriate test credentials** from service providers
2. **Secure your CI/CD secret storage** properly
3. **Rotate secrets regularly** according to your security policy
4. **Never use production secrets** in test packages
5. **Review test package code** before running with your secrets

## Examples

### Payment Gateway Testing

```json
{
  "package": "payment-gateway-tests",
  "requires": {
    "secrets": [
      "STRIPE_TEST_SECRET_KEY",
      "PAYPAL_SANDBOX_CLIENT_ID",
      "PAYPAL_SANDBOX_SECRET",
      "SQUARE_SANDBOX_ACCESS_TOKEN"
    ]
  }
}
```

### API Integration Testing

```json
{
  "package": "api-integration-tests",
  "requires": {
    "secrets": [
      "API_BASE_URL",
      "API_CLIENT_ID",
      "API_CLIENT_SECRET",
      "API_REFRESH_TOKEN"
    ]
  }
}
```

### Multi-Environment Testing

```json
{
  "package": "multi-env-tests",
  "requires": {
    "secrets": [
      "STAGING_API_KEY",
      "STAGING_API_SECRET",
      "SANDBOX_API_KEY",
      "SANDBOX_API_SECRET"
    ]
  }
}
```

## Migration Guide

If you have existing test packages that handle secrets:

### Before (Insecure)

```json
{
  "test": {
    "phases": {
      "setup": [
        "export STRIPE_KEY=sk_test_hardcoded123",
        "./setup.sh"
      ]
    }
  }
}
```

### After (Secure)

```json
{
  "requires": {
    "secrets": ["STRIPE_TEST_SECRET_KEY"]
  },
  "test": {
    "phases": {
      "setup": ["./setup.sh"]
    }
  }
}
```

## Support

For issues or questions about secret handling:

1. Check this documentation
2. Review the [troubleshooting](#troubleshooting) section
3. Report issues at: https://github.com/woocommerce/qit-cli/issues
4. Contact support with security concerns (do NOT include actual secret values)

## Related Documentation

- [Test Package Manifest Schema](./MANIFEST_SCHEMA.md)
- [Lifecycle Phases](./LIFECYCLE_PHASES.md)
- [CTRF Reporting](./CTRF_REPORTING.md)
- [CI/CD Integration](./CI_INTEGRATION.md)