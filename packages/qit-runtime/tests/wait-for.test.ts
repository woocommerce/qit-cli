import { describe, it, expect } from 'vitest';
import { waitFor } from '../src/wait-for.js';

describe('qit.waitFor', () => {
  it('resolves immediately when condition is true', async () => {
    await waitFor(() => true);
  });

  it('resolves when condition becomes true', async () => {
    let count = 0;
    await waitFor(() => {
      count++;
      return count >= 3;
    }, 5000, 10);
    expect(count).toBe(3);
  });

  it('throws on timeout', async () => {
    await expect(
      waitFor(() => false, 50, 10),
    ).rejects.toThrow('waitFor timed out after 50ms');
  });

  it('works with async conditions', async () => {
    let count = 0;
    await waitFor(async () => {
      count++;
      return count >= 2;
    }, 5000, 10);
    expect(count).toBe(2);
  });
});
