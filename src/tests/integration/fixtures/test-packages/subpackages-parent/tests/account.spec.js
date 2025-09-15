import { test, expect } from '@playwright/test';

test('my account test', async ({ page }) => {
  console.log('[TEST] Running account subpackage test');
  await page.goto('/my-account');
  await expect(page.locator('body')).toBeVisible();
  
  // Log to verify this specific test ran
  console.log('ACCOUNT_SUBPACKAGE_EXECUTED');
  expect(true).toBe(true);
});