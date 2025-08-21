import { test, expect } from '@playwright/test';

test.describe('My E2E Tests', () => {
  test('should load the homepage', async ({ page }) => {
    await page.goto('/');
    // Check that page has a title (works with any test site name)
    const title = await page.title();
    expect(title).toBeTruthy();
    expect(title.length).toBeGreaterThan(0);
    console.log('Homepage loaded successfully with title:', title);
  });

  test('should have shop page', async ({ page }) => {
    await page.goto('/shop');
    const heading = page.locator('h1');
    await expect(heading).toBeVisible();
    console.log('Shop page loaded');
  });

  test('should access admin dashboard', async ({ page }) => {
    // Go to admin dashboard
    await page.goto('/wp-admin');
    
    // Check if we're redirected to login
    if (page.url().includes('wp-login.php')) {
      console.log('Need to login first');
      // Use default credentials
      await page.fill('#user_login', 'admin');
      await page.fill('#user_pass', 'password');
      await page.click('#wp-submit');
      // Wait for navigation after login
      await page.waitForURL('**/wp-admin/**');
    }
    
    // Check we're in the dashboard by looking for the dashboard widget
    const dashboardWidget = page.locator('#dashboard-widgets-wrap, .wrap h1').first();
    await expect(dashboardWidget).toBeVisible();
    console.log('Admin dashboard accessible');
  });
});