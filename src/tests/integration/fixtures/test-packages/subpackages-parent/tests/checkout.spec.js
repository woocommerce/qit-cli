import { test, expect } from '@playwright/test';

test('checkout flow test', async ({ page }) => {
  console.log('[TEST] Running checkout subpackage test');
  await page.goto('/checkout');
  await expect(page.locator('body')).toBeVisible();
  
  // Log to verify this specific test ran
  console.log('CHECKOUT_SUBPACKAGE_EXECUTED');
  expect(true).toBe(true);
});