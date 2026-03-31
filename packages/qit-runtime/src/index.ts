/**
 * @qit/runtime — QIT runtime for test packages.
 *
 * Two access patterns:
 *   qit.providers('makePurchase')          — anonymous capability discovery (cumulative)
 *   qit.package('woocommerce/core-utils')  — known dependency access (singular)
 *
 * Three availability tiers:
 *   Always available:  env.isQit, providers(), hasProvider(), package(), waitFor(), version
 *   Needs env vars:    env.siteUrl, env.db.*, env.sut.*, etc.
 *   Needs Docker:      wp(), exec()
 */

import { env } from './env.js';
import { wp, exec } from './docker.js';
import { providers, hasProvider } from './providers.js';
import type { ProviderFunction } from './providers.js';
import { loadPackage } from './packages.js';
import { waitFor } from './wait-for.js';

export type { ProviderFunction };

export const qit = {
  env,
  wp,
  exec,
  providers,
  hasProvider,
  package: loadPackage,
  waitFor,
  version: '0.1.0',
};

export default qit;
