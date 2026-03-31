import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import { env } from '../src/env.js';

describe('qit.env', () => {
  const originalEnv = { ...process.env };

  beforeEach(() => {
    for (const key of Object.keys(process.env)) {
      if (key.startsWith('QIT')) {
        delete process.env[key];
      }
    }
  });

  afterEach(() => {
    process.env = { ...originalEnv };
  });

  describe('isQit', () => {
    it('returns false when QIT is not set', () => {
      expect(env.isQit).toBe(false);
    });

    it('returns true when QIT=1', () => {
      process.env.QIT = '1';
      expect(env.isQit).toBe(true);
    });

    it('returns false when QIT is something other than 1', () => {
      process.env.QIT = 'yes';
      expect(env.isQit).toBe(false);
    });
  });

  describe('required env vars', () => {
    it('returns value when set', () => {
      process.env.QIT_SITE_URL = 'http://localhost:8080';
      expect(env.siteUrl).toBe('http://localhost:8080');
    });

    it('throws when not set', () => {
      expect(() => env.siteUrl).toThrow('QIT_SITE_URL is not set');
    });

    it('throws when empty string', () => {
      process.env.QIT_SITE_URL = '';
      expect(() => env.siteUrl).toThrow('QIT_SITE_URL is not set');
    });
  });

  describe('nested objects', () => {
    it('reads db vars', () => {
      process.env.QIT_DB_HOST = 'localhost';
      process.env.QIT_DB_NAME = 'wordpress';
      expect(env.db.host).toBe('localhost');
      expect(env.db.name).toBe('wordpress');
    });

    it('reads container vars', () => {
      process.env.QIT_PHP_CONTAINER = 'qit_env_php_abc';
      expect(env.containers.php).toBe('qit_env_php_abc');
    });
  });

  describe('comma-separated lists', () => {
    it('splits into array', () => {
      process.env.QIT_ACTIVE_PLUGINS = 'woocommerce,my-plugin,stripe';
      expect(env.plugins.active).toEqual(['woocommerce', 'my-plugin', 'stripe']);
    });

    it('returns empty array when not set', () => {
      expect(env.plugins.active).toEqual([]);
    });

    it('trims whitespace', () => {
      process.env.QIT_ACTIVE_PLUGINS = ' woocommerce , my-plugin ';
      expect(env.plugins.active).toEqual(['woocommerce', 'my-plugin']);
    });
  });

  describe('no caching', () => {
    it('reflects env var changes between accesses', () => {
      process.env.QIT_SITE_URL = 'http://first:8080';
      expect(env.siteUrl).toBe('http://first:8080');

      process.env.QIT_SITE_URL = 'http://second:9090';
      expect(env.siteUrl).toBe('http://second:9090');
    });
  });
});
