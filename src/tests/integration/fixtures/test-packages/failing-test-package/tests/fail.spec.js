import { test, expect } from '@playwright/test';

test.describe('Mixed Results Tests', () => {
  test('should pass - homepage loads', async ({ page }) => {
    await page.goto('/');
    // Check that page has a title (works with any test site name)
    const title = await page.title();
    expect(title).toBeTruthy();
    expect(title.length).toBeGreaterThan(0);
    console.log('Homepage loaded successfully with title:', title);
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