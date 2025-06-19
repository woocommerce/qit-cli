// Example E2E test file
import { test, expect } from '@playwright/test';

test('Example test', async ({ page }) => {
  console.log('Running example test');
  expect(1).toBe(1);
});