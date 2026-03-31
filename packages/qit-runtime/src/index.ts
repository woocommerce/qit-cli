/**
 * @woocommerce/qit-runtime — QIT runtime for test packages.
 *
 * Two access patterns:
 *   qit.actions('makePurchase')             — anonymous capability discovery (cumulative)
 *   qit.package('woocommerce/core-utils')   — known dependency access (singular)
 *
 * Three availability tiers:
 *   Always available:  env.isQit, actions(), hasAction(), package(), waitFor(), version
 *   Needs env vars:    env.siteUrl, env.db.*, env.sut.*, etc.
 *   Needs Docker:      wp(), exec()
 */

import { env } from './env.js';
import { wp, exec } from './docker.js';
import { actions, hasAction } from './actions.js';
import type { ActionFunction } from './actions.js';
import { loadPackage } from './packages.js';
import { waitFor } from './wait-for.js';

export type { ActionFunction };

export const qit = {
  env,
  wp,
  exec,
  actions,
  hasAction,
  package: loadPackage,
  waitFor,
  version: '0.1.1',
};

export default qit;
