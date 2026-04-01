/**
 * Execute commands inside QIT Docker containers.
 *
 * Needs Docker tier: throws if QIT_PHP_CONTAINER is not set.
 */

import { execSync } from 'child_process';

const DEFAULT_TIMEOUT = 300_000; // 5 minutes

export interface ExecOptions {
  /** Timeout in milliseconds. Default: 300000 (5 minutes). */
  timeout?: number;
}

function getPhpContainer(): string {
  const container = process.env.QIT_PHP_CONTAINER;
  if (!container) {
    throw new Error(
      'QIT_PHP_CONTAINER is not set.\n' +
      'Docker commands require a running QIT environment.\n' +
      'Run: qit env:up + source $(qit env:source)'
    );
  }
  return container;
}

/**
 * Execute a WP-CLI command inside the PHP container.
 *
 * @param command - The WP-CLI command without the leading `wp` (e.g., `'plugin list --format=json'`).
 * @param options - Optional configuration (timeout).
 * @returns stdout trimmed.
 */
export async function wp(command: string, options?: ExecOptions): Promise<string> {
  const container = getPhpContainer();
  const timeout = options?.timeout ?? DEFAULT_TIMEOUT;
  const result = execSync(
    `docker exec ${container} wp ${command} --allow-root`,
    { encoding: 'utf-8', timeout }
  );
  return result.trim();
}

/**
 * Execute an arbitrary command inside the PHP container.
 *
 * @param command - The shell command to run.
 * @param options - Optional configuration (timeout).
 * @returns stdout trimmed.
 */
export async function exec(command: string, options?: ExecOptions): Promise<string> {
  const container = getPhpContainer();
  const timeout = options?.timeout ?? DEFAULT_TIMEOUT;
  const result = execSync(
    `docker exec ${container} ${command}`,
    { encoding: 'utf-8', timeout }
  );
  return result.trim();
}
