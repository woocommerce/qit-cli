/**
 * Typed wrappers over QIT_* environment variables.
 *
 * Three tiers:
 *   - Always available: `isQit`
 *   - Needs env vars (any source): everything else — throws if the var is missing
 *   - (Docker tier is handled by docker.ts, not here)
 */

function requireEnv(name: string): string {
  const value = process.env[name];
  if (value === undefined || value === '') {
    throw new Error(
      `${name} is not set.\n` +
      `Set it manually, or run tests via: qit run:e2e / qit env:up + source $(qit env:source)`
    );
  }
  return value;
}

function optionalEnv(name: string): string {
  return process.env[name] ?? '';
}

function commaSplit(name: string): string[] {
  const raw = optionalEnv(name);
  return raw ? raw.split(',').map(s => s.trim()).filter(Boolean) : [];
}

export const env = {
  /** True when QIT=1 is set. Safe to call in any context. */
  get isQit(): boolean {
    return process.env.QIT === '1';
  },

  get id(): string { return requireEnv('QIT_ENV_ID'); },
  get siteUrl(): string { return requireEnv('QIT_SITE_URL'); },
  get adminUrl(): string { return requireEnv('QIT_WP_ADMIN'); },
  get baseUrl(): string { return requireEnv('QIT_BASE_URL'); },

  db: {
    get host(): string { return requireEnv('QIT_DB_HOST'); },
    get name(): string { return requireEnv('QIT_DB_NAME'); },
    get user(): string { return requireEnv('QIT_DB_USER'); },
    get password(): string { return requireEnv('QIT_DB_PASSWORD'); },
  },

  wp: {
    get username(): string { return requireEnv('QIT_WP_USERNAME'); },
    get password(): string { return requireEnv('QIT_WP_PASSWORD'); },
  },

  containers: {
    get php(): string { return requireEnv('QIT_PHP_CONTAINER'); },
    get db(): string { return requireEnv('QIT_DB_CONTAINER'); },
  },

  sut: {
    get slug(): string { return requireEnv('QIT_SUT_SLUG'); },
    get type(): string { return requireEnv('QIT_SUT_TYPE'); },
    get entrypoint(): string { return requireEnv('QIT_SUT_ENTRYPOINT'); },
  },

  versions: {
    get wp(): string { return requireEnv('QIT_WP_VERSION'); },
    get woo(): string { return requireEnv('QIT_WOO_VERSION'); },
    get php(): string { return requireEnv('QIT_PHP_VERSION'); },
  },

  plugins: {
    get active(): string[] { return commaSplit('QIT_ACTIVE_PLUGINS'); },
    get additional(): string[] { return commaSplit('QIT_ADDITIONAL_PLUGINS'); },
  },

  themes: {
    get additional(): string[] { return commaSplit('QIT_ADDITIONAL_THEMES'); },
  },

  get testPackages(): string[] { return commaSplit('QIT_TEST_PACKAGES'); },
};
