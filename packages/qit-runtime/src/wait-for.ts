/**
 * Generic async condition polling.
 *
 * Always available — no QIT env vars needed.
 */

/**
 * Wait for a condition to become true, polling at a fixed interval.
 *
 * @param condition - Function that returns true (or resolves to true) when the condition is met.
 * @param timeout - Maximum time to wait in milliseconds. Default: 30000.
 * @param interval - Polling interval in milliseconds. Default: 1000.
 * @throws If the timeout is reached before the condition is met.
 */
export async function waitFor(
  condition: () => boolean | Promise<boolean>,
  timeout = 30_000,
  interval = 1_000,
): Promise<void> {
  const start = Date.now();

  while (Date.now() - start < timeout) {
    if (await condition()) {
      return;
    }
    await new Promise(resolve => setTimeout(resolve, interval));
  }

  throw new Error(`waitFor timed out after ${timeout}ms`);
}
