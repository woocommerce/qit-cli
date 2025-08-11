import { test, expect } from '@playwright/test';

test('cart functionality test', async ({ page }) => {
  console.log('[TEST] Running cart subpackage test');
  await page.goto('/cart');
  await expect(page.locator('body')).toBeVisible();
  
  // Log to verify this specific test ran
  console.log('CART_SUBPACKAGE_EXECUTED');
  expect(true).toBe(true);
});