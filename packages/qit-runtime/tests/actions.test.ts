import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import { actions, hasAction } from '../src/actions.js';
import { writeFileSync, mkdirSync, rmSync } from 'fs';
import { join } from 'path';
import { tmpdir } from 'os';

describe('qit.actions / qit.hasAction', () => {
  const originalEnv = { ...process.env };
  let tempDir: string;

  beforeEach(() => {
    delete process.env.QIT_ACTIONS_MANIFEST;
    tempDir = join(tmpdir(), `qit-test-${Date.now()}-${Math.random().toString(36).slice(2)}`);
    mkdirSync(tempDir, { recursive: true });
  });

  afterEach(() => {
    process.env = { ...originalEnv };
    rmSync(tempDir, { recursive: true, force: true });
  });

  it('returns [] when QIT_ACTIONS_MANIFEST is not set', () => {
    expect(actions('makePurchase')).toEqual([]);
  });

  it('hasAction returns false when no manifest', () => {
    expect(hasAction('makePurchase')).toBe(false);
  });

  it('returns [] for unknown action even with manifest', () => {
    const actionFile = join(tempDir, 'pay.cjs');
    writeFileSync(actionFile, 'module.exports = function makePurchase() {}; module.exports.default = module.exports;');

    const manifest = {
      makePurchase: [{ provider: 'stripe/payments', path: actionFile }],
    };
    const manifestPath = join(tempDir, 'manifest.json');
    writeFileSync(manifestPath, JSON.stringify(manifest));
    process.env.QIT_ACTIONS_MANIFEST = manifestPath;

    expect(actions('unknownAction')).toEqual([]);
  });
});
