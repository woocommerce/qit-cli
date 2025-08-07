/**
 * QIT Helper Functions Type Definitions
 * 
 * Type definitions for qit-helpers.js to provide better IDE support
 * in TypeScript and JavaScript projects.
 */

/**
 * Execute a WP-CLI command inside the WordPress container.
 */
export function execWpCli(command: string): Promise<string>;

/**
 * Execute a raw command inside the WordPress container.
 */
export function execInContainer(command: string): Promise<string>;

/**
 * Get the site URL for the test environment.
 */
export function getSiteUrl(): string;

/**
 * Get the WordPress admin URL.
 */
export function getAdminUrl(): string;

/**
 * Database connection details.
 */
export interface DbConnection {
    host: string;
    port: number;
    database: string;
    user: string;
    password: string;
}

/**
 * Get database connection details.
 */
export function getDbConnection(): DbConnection;

/**
 * Admin credentials.
 */
export interface AdminCredentials {
    username: string;
    password: string;
}

/**
 * Get WordPress admin credentials.
 */
export function getAdminCredentials(): AdminCredentials;

/**
 * Check if running in a QIT environment.
 */
export function isQitEnvironment(): boolean;

/**
 * Get the QIT environment ID.
 */
export function getEnvironmentId(): string | null;

/**
 * Wait for a condition to be true.
 */
export function waitFor(
    condition: () => boolean | Promise<boolean>,
    timeout?: number,
    interval?: number
): Promise<void>;

/**
 * Install and activate a plugin.
 */
export function installPlugin(plugin: string): Promise<void>;

/**
 * Create a test user.
 */
export function createUser(
    username: string,
    email: string,
    role?: string
): Promise<number>;

/**
 * Clear all transients.
 */
export function clearTransients(): Promise<void>;

/**
 * Flush rewrite rules.
 */
export function flushRewriteRules(): Promise<void>;