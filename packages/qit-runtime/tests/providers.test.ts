import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import { providers, hasProvider } from '../src/providers.js';
import { writeFileSync, mkdirSync, rmSync } from 'fs';
import { join } from 'path';
import { tmpdir } from 'os';

describe('qit.providers / qit.hasProvider', () => {
  const originalEnv = { ...process.env };
  let tempDir: string;

  beforeEach(() => {
    delete process.env.QIT_PROVIDERS_MANIFEST;
    // Reset the module-level cache by re-importing would be ideal,
    // but since the cache is module-scoped, we work around it by
    // using unique temp dirs per test.
    tempDir = join(tmpdir(), `qit-test-${Date.now()}-${Math.random().toString(36).slice(2)}`);
    mkdirSync(tempDir, { recursive: true });
  });

  afterEach(() => {
    process.env = { ...originalEnv };
    rmSync(tempDir, { recursive: true, force: true });
  });

  it('returns [] when QIT_PROVIDERS_MANIFEST is not set', () => {
    expect(providers('makePurchase')).toEqual([]);
  });

  it('hasProvider returns false when no manifest', () => {
    expect(hasProvider('makePurchase')).toBe(false);
  });

  it('returns [] for unknown capability even with manifest', () => {
    // Create a manifest with one capability
    const providerFile = join(tempDir, 'pay.cjs');
    writeFileSync(providerFile, 'module.exports = function makePurchase() {}; module.exports.default = module.exports;');

    const manifest = {
      makePurchase: [{ provider: 'stripe/payments', path: providerFile }],
    };
    const manifestPath = join(tempDir, 'manifest.json');
    writeFileSync(manifestPath, JSON.stringify(manifest));
    process.env.QIT_PROVIDERS_MANIFEST = manifestPath;

    expect(providers('unknownCapability')).toEqual([]);
  });
});
