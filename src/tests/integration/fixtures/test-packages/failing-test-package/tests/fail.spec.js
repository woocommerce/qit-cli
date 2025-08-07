import { test, expect } from '@playwright/test';

test.describe('Mixed Results Tests', () => {
  test('should pass - homepage loads', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/WooCommerce/);
    console.log('Homepage loaded successfully');
  });

  test('should pass - shop page exists', async ({ page }) => {
    await page.goto('/shop');
    await expect(page.locator('body')).toBeVisible();
    console.log('Shop page loaded');
  });

  test('should FAIL - non-existent element', async ({ page }) => {
    await page.goto('/');
    // This will fail and generate a screenshot
    await expect(page.locator('#this-element-does-not-exist')).toBeVisible();
    console.log('This should not print');
  });
});