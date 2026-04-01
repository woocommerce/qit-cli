/**
 * Sync test: ensures env.ts exposes every QIT_* variable that EnvironmentVars.php sets.
 *
 * Source of truth: EnvironmentVars.php's get_mapping() method.
 * If this test fails, someone added/removed a var in PHP but not in env.ts.
 */

import { describe, it, expect } from 'vitest';
import { readFileSync } from 'fs';
import { resolve } from 'path';

// Path to the PHP source of truth
const ENV_VARS_PHP = resolve(__dirname, '../../../src/src/Environment/EnvironmentVars.php');

// Extract all QIT_* variable names from the PHP file
function extractPhpEnvVarNames(): string[] {
  const php = readFileSync(ENV_VARS_PHP, 'utf-8');

  // Match string keys like 'QIT_SITE_URL', 'QIT_DB_HOST', etc.
  // Covers both the $vars array and later $vars['QIT_...'] assignments.
  const matches = php.matchAll(/['"]QIT_([A-Z_]+)['"]/g);
  const names = new Set<string>();

  for (const m of matches) {
    names.add(`QIT_${m[1]}`);
  }

  return [...names].sort();
}

// Map from QIT_* env var name to the env.ts property path that reads it.
// This IS the mapping we must keep in sync.
const ENV_TS_MAPPING: Record<string, string> = {
  'QIT':                    'env.isQit',
  'QIT_ENV_ID':             'env.id',
  'QIT_SITE_URL':           'env.siteUrl',
  'QIT_WP_ADMIN':           'env.adminUrl',
  'QIT_BASE_URL':           'env.baseUrl',
  'QIT_WP_ADMIN_URL':       'env.adminUrl',       // alias of QIT_WP_ADMIN, same getter
  'QIT_DB_HOST':            'env.db.host',
  'QIT_DB_NAME':            'env.db.name',
  'QIT_DB_USER':            'env.db.user',
  'QIT_DB_PASSWORD':        'env.db.password',
  'QIT_WP_USERNAME':        'env.wp.username',
  'QIT_WP_PASSWORD':        'env.wp.password',
  'QIT_PHP_CONTAINER':      'env.containers.php',
  'QIT_DB_CONTAINER':       'env.containers.db',
  'QIT_SUT_SLUG':           'env.sut.slug',
  'QIT_SUT_TYPE':           'env.sut.type',
  'QIT_SUT_ENTRYPOINT':     'env.sut.entrypoint',
  'QIT_ACTIVE_PLUGINS':     'env.plugins.active',
  'QIT_ADDITIONAL_PLUGINS': 'env.plugins.additional',
  'QIT_ADDITIONAL_THEMES':  'env.themes.additional',
  'QIT_TEST_PACKAGES':      'env.testPackages',
  'QIT_WP_VERSION':         'env.versions.wp',
  'QIT_WOO_VERSION':        'env.versions.woo',
  'QIT_PHP_VERSION':        'env.versions.php',
};

describe('env.ts ↔ EnvironmentVars.php sync', () => {
  it('env.ts covers every QIT_* variable set by the PHP CLI', () => {
    const phpVars = extractPhpEnvVarNames();
    const mappedVars = Object.keys(ENV_TS_MAPPING).sort();

    const unmapped = phpVars.filter(v => !mappedVars.includes(v));

    expect(unmapped).toEqual([]);
  });

  it('env.ts does not reference QIT_* variables that the PHP CLI does not set', () => {
    const phpVars = extractPhpEnvVarNames();
    const mappedVars = Object.keys(ENV_TS_MAPPING);

    // Also include 'QIT' itself (the boolean flag)
    const allPhpVars = new Set([...phpVars, 'QIT']);

    const stale = mappedVars.filter(v => !allPhpVars.has(v));

    expect(stale).toEqual([]);
  });
});
