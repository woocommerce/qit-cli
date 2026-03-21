# AI Shell Isolation Rules

When running QIT commands in an AI assistant context (Claude Code, etc.), each command executes in a **fresh, isolated shell**. Environment variables set in one command do NOT persist to the next.

## The Core Rule

**You MUST source the QIT environment for EVERY command that needs it:**

```bash
# CORRECT - source for every command
source $(qit env:source <env-id>) && npx playwright test
source $(qit env:source <env-id>) && echo $QIT_SITE_URL
source $(qit env:source <env-id>) && curl -s $QIT_SITE_URL/wp-json/wp/v2/plugins

# WRONG - assumes environment persists between commands
npx playwright test  # QIT_SITE_URL is empty!
```

## Command Execution Context

QIT test packages use two execution contexts:

- **Host commands** (`npm`, `npx`, commands starting with `host:`) - Run on the host machine where Node.js is available
- **Docker commands** (everything else, `.sh` scripts) - Run inside the WordPress Docker container where WP-CLI is available

This detection is automatic. You don't need to specify it unless overriding with the `runs_on` field in `qit-test.json`.

## When Sourcing Is NOT Needed

- `qit run:e2e` - QIT manages the entire lifecycle automatically
- `qit env:list` - Reads from QIT's own state
- `qit env:up` / `qit env:down` - Lifecycle commands
- Any `qit` command that doesn't need environment variables

## When Sourcing IS Needed

- `npx playwright test` - Needs `$QIT_SITE_URL`, `$QIT_ADMIN_USERNAME`, etc.
- `curl $QIT_SITE_URL/...` - Needs the site URL
- Any command that reads QIT environment variables

## WP-CLI Commands

Use `qit env:exec` which handles the Docker context automatically:

```bash
qit env:exec <env-id> "wp plugin list"
qit env:exec <env-id> "wp db query 'SELECT * FROM wp_options LIMIT 5'"
```

## Available Environment Variables

After sourcing, these variables are available:
- `$QIT_SITE_URL` - The WordPress site URL
- `$QIT_ADMIN_USERNAME` - Admin username
- `$QIT_ADMIN_PASSWORD` - Admin password
- `$QIT_ENV_ID` - Current environment ID
