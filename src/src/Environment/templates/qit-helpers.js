/**
 * QIT Helper Functions for Test Packages
 * 
 * This file provides utility functions for test packages to interact with
 * the QIT environment, particularly for executing WP-CLI commands.
 * 
 * Usage in your test:
 *   const { execWpCli, getSiteUrl, getDbConnection } = require('./qit-helpers');
 *   
 *   // Run a WP-CLI command
 *   const result = await execWpCli('plugin list --format=json');
 *   const plugins = JSON.parse(result);
 */

const { exec } = require('child_process');
const { promisify } = require('util');
const execAsync = promisify(exec);

/**
 * Execute a WP-CLI command inside the WordPress container.
 * 
 * @param {string} command - The WP-CLI command to execute (without 'wp' prefix)
 * @returns {Promise<string>} - The command output
 * @throws {Error} - If the command fails
 * 
 * @example
 * const users = await execWpCli('user list --format=json');
 * const posts = await execWpCli('post list --status=publish');
 */
async function execWpCli(command) {
    const phpContainer = process.env.PHP_CONTAINER || process.env.QIT_PHP_CONTAINER;
    
    if (!phpContainer) {
        throw new Error('PHP_CONTAINER environment variable not set. Are you running this inside a QIT environment?');
    }
    
    const dockerCommand = `docker exec ${phpContainer} wp ${command} --allow-root`;
    
    try {
        const { stdout, stderr } = await execAsync(dockerCommand);
        if (stderr && !stderr.includes('Warning:')) {
            console.error('WP-CLI stderr:', stderr);
        }
        return stdout.trim();
    } catch (error) {
        throw new Error(`WP-CLI command failed: ${error.message}`);
    }
}

/**
 * Execute a raw command inside the WordPress container.
 * 
 * @param {string} command - The command to execute
 * @returns {Promise<string>} - The command output
 * @throws {Error} - If the command fails
 * 
 * @example
 * const phpVersion = await execInContainer('php --version');
 */
async function execInContainer(command) {
    const phpContainer = process.env.PHP_CONTAINER || process.env.QIT_PHP_CONTAINER;
    
    if (!phpContainer) {
        throw new Error('PHP_CONTAINER environment variable not set. Are you running this inside a QIT environment?');
    }
    
    const dockerCommand = `docker exec ${phpContainer} ${command}`;
    
    try {
        const { stdout, stderr } = await execAsync(dockerCommand);
        if (stderr) {
            console.error('Container stderr:', stderr);
        }
        return stdout.trim();
    } catch (error) {
        throw new Error(`Container command failed: ${error.message}`);
    }
}

/**
 * Get the site URL for the test environment.
 * 
 * @returns {string} - The site URL
 */
function getSiteUrl() {
    return process.env.QIT_SITE_URL || process.env.BASE_URL || 'http://localhost:8080';
}

/**
 * Get the WordPress admin URL.
 * 
 * @returns {string} - The admin URL
 */
function getAdminUrl() {
    return process.env.QIT_WP_ADMIN || process.env.WP_ADMIN_URL || `${getSiteUrl()}/wp-admin`;
}

/**
 * Get database connection details.
 * 
 * @returns {Object} - Database connection info
 */
function getDbConnection() {
    return {
        host: process.env.DB_HOST || 'localhost',
        port: parseInt(process.env.DB_PORT || '3306'),
        database: process.env.DB_NAME || 'wordpress',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || 'root'
    };
}

/**
 * Get WordPress admin credentials.
 * 
 * @returns {Object} - Admin credentials
 */
function getAdminCredentials() {
    return {
        username: process.env.WP_USERNAME || 'admin',
        password: process.env.WP_PASSWORD || 'password'
    };
}

/**
 * Check if running in a QIT environment.
 * 
 * @returns {boolean} - True if in QIT environment
 */
function isQitEnvironment() {
    return !!(process.env.QIT_ENV_ID && process.env.QIT_SITE_URL);
}

/**
 * Get the QIT environment ID.
 * 
 * @returns {string|null} - The environment ID or null
 */
function getEnvironmentId() {
    return process.env.QIT_ENV_ID || null;
}

/**
 * Wait for a condition to be true.
 * 
 * @param {Function} condition - Function that returns true when condition is met
 * @param {number} timeout - Maximum time to wait in milliseconds (default 30000)
 * @param {number} interval - Check interval in milliseconds (default 1000)
 * @returns {Promise<void>}
 * @throws {Error} - If timeout is reached
 */
async function waitFor(condition, timeout = 30000, interval = 1000) {
    const startTime = Date.now();
    
    while (Date.now() - startTime < timeout) {
        if (await condition()) {
            return;
        }
        await new Promise(resolve => setTimeout(resolve, interval));
    }
    
    throw new Error(`Timeout waiting for condition after ${timeout}ms`);
}

/**
 * Install and activate a plugin.
 * 
 * @param {string} plugin - Plugin slug or URL
 * @returns {Promise<void>}
 * 
 * @example
 * await installPlugin('woocommerce');
 * await installPlugin('https://example.com/plugin.zip');
 */
async function installPlugin(plugin) {
    await execWpCli(`plugin install ${plugin} --activate`);
}

/**
 * Create a test user.
 * 
 * @param {string} username - Username
 * @param {string} email - Email address
 * @param {string} role - User role (default 'subscriber')
 * @returns {Promise<number>} - User ID
 */
async function createUser(username, email, role = 'subscriber') {
    const result = await execWpCli(
        `user create ${username} ${email} --role=${role} --porcelain`
    );
    return parseInt(result);
}

/**
 * Clear all transients.
 * 
 * @returns {Promise<void>}
 */
async function clearTransients() {
    await execWpCli('transient delete --all');
}

/**
 * Flush rewrite rules.
 * 
 * @returns {Promise<void>}
 */
async function flushRewriteRules() {
    await execWpCli('rewrite flush');
}

module.exports = {
    execWpCli,
    execInContainer,
    getSiteUrl,
    getAdminUrl,
    getDbConnection,
    getAdminCredentials,
    isQitEnvironment,
    getEnvironmentId,
    waitFor,
    installPlugin,
    createUser,
    clearTransients,
    flushRewriteRules
};