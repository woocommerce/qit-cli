# QIT Helpers for Test Packages

This directory contains helper utilities for test packages to interact with QIT environments.

## Files

- `qit-helpers.js` - JavaScript helper functions for executing WP-CLI commands and other QIT operations
- `qit-helpers.d.ts` - TypeScript definitions for better IDE support

## Usage in Test Packages

### Basic Setup

1. Copy `qit-helpers.js` to your test package directory
2. (Optional) Copy `qit-helpers.d.ts` for TypeScript support

### Using in Playwright Tests

```javascript
// In your Playwright test file
const { execWpCli, getSiteUrl, getAdminCredentials } = require('./qit-helpers');
const { test, expect } = require('@playwright/test');

// Example: Branch logic based on QIT context
const siteUrl = process.env.QIT 
    ? process.env.QIT_SITE_URL  // Running in QIT
    : 'http://localhost:8080';   // Local development

test('verify plugin activation', async ({ page }) => {
    // Execute WP-CLI command
    const plugins = JSON.parse(await execWpCli('plugin list --format=json'));
    const myPlugin = plugins.find(p => p.name === 'my-plugin');
    expect(myPlugin.status).toBe('active');
    
    // Navigate to site
    await page.goto(getSiteUrl());
    
    // Login as admin
    const creds = getAdminCredentials();
    await page.goto(getSiteUrl() + '/wp-login.php');
    await page.fill('#user_login', creds.username);
    await page.fill('#user_pass', creds.password);
    await page.click('#wp-submit');
});
```

### Using Environment Variables Directly

If you prefer not to use the helper functions, you can access the environment variables directly:

```javascript
test('direct env var usage', async ({ page }) => {
    const siteUrl = process.env.QIT_SITE_URL || process.env.BASE_URL;
    await page.goto(siteUrl);
});
```

## Available Environment Variables

When running in a QIT environment, the following variables are available:

- `QIT` - Set to "1" when running in QIT context (useful for branching logic)
- `QIT_ENV_ID` - Unique environment identifier
- `QIT_SITE_URL` - WordPress site URL
- `QIT_WP_ADMIN` - WordPress admin URL
- `BASE_URL` - Alias for QIT_SITE_URL (Playwright standard)
- `WP_ADMIN_URL` - Alias for QIT_WP_ADMIN
- `DB_HOST` - Database host
- `DB_PORT` - Database port
- `DB_NAME` - Database name
- `DB_USER` - Database username
- `DB_PASSWORD` - Database password
- `WP_USERNAME` - WordPress admin username
- `WP_PASSWORD` - WordPress admin password
- `PHP_CONTAINER` - PHP container name (for docker exec)
- `DB_CONTAINER` - Database container name

## Manual Testing

For manual testing outside of the orchestrator, you can load these environment variables:

```bash
# Start an environment
qit env:up

# Load environment variables
source "$(qit env:source)"

# Now you can run your tests manually
npm test
```

## Helper Functions Reference

### WP-CLI Execution

- `execWpCli(command)` - Execute a WP-CLI command
- `execInContainer(command)` - Execute any command in the container

### Environment Info

- `getSiteUrl()` - Get the site URL
- `getAdminUrl()` - Get the admin URL
- `getDbConnection()` - Get database connection details
- `getAdminCredentials()` - Get admin login credentials
- `isQitEnvironment()` - Check if running in QIT
- `getEnvironmentId()` - Get the environment ID

### Utility Functions

- `waitFor(condition, timeout, interval)` - Wait for a condition
- `installPlugin(plugin)` - Install and activate a plugin
- `createUser(username, email, role)` - Create a test user
- `clearTransients()` - Clear all transients
- `flushRewriteRules()` - Flush rewrite rules

## Example: Branching Logic Based on Context

```javascript
// config.js - Configuration that adapts to the environment
module.exports = {
    // Use QIT environment variables when available, fallback to local defaults
    siteUrl: process.env.QIT_SITE_URL || 'http://localhost:8888',
    adminUser: process.env.WP_USERNAME || 'admin',
    adminPass: process.env.WP_PASSWORD || 'password',
    
    // Different timeouts for QIT vs local
    timeout: process.env.QIT ? 30000 : 60000,
    
    // Skip certain tests in QIT
    skipFlaky: process.env.QIT === '1',
    
    // Use container commands in QIT, local commands otherwise
    wpCliCommand: process.env.QIT 
        ? `docker exec ${process.env.PHP_CONTAINER} wp`
        : 'wp'
};
```

## Example: Complete Test Package

```javascript
// test.spec.js
const { test, expect } = require('@playwright/test');
const { 
    execWpCli, 
    getSiteUrl, 
    getAdminCredentials,
    waitFor,
    installPlugin 
} = require('./qit-helpers');

// Skip certain tests when not in QIT
const describeInQit = process.env.QIT ? test.describe : test.describe.skip;

test.describe('My Plugin Tests', () => {
    test.beforeAll(async () => {
        // Setup: Install required plugins
        await installPlugin('woocommerce');
        
        // Create test data
        await execWpCli('post create --post_title="Test Post" --post_status=publish');
    });
    
    test('plugin functionality', async ({ page }) => {
        // Your test code here
        await page.goto(getSiteUrl());
        // ...
    });
    
    test.afterAll(async () => {
        // Cleanup
        await execWpCli('post delete $(wp post list --format=ids) --force');
    });
});
```