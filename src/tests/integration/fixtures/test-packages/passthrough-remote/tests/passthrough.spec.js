import { test, expect } from '@playwright/test';

test.describe('Passthrough Remote Tests', () => {
  // This test has @remote-only tag and should only run when --grep=@remote-only is passed
  test('should run with @remote-only tag', { tag: '@remote-only' }, async ({ page }) => {
    await page.goto('/');
    const title = await page.title();
    expect(title).toBeTruthy();
    console.log('REMOTE: Test with @remote-only tag executed');
  });

  // This test has @shared tag and should run when --grep=@shared is passed
  test('should run with @shared tag', { tag: '@shared' }, async ({ page }) => {
    await page.goto('/');
    const title = await page.title();
    expect(title).toBeTruthy();
    console.log('REMOTE: Test with @shared tag executed');
  });

  // This test has @grep-test tag for testing grep functionality
  test('should run with @grep-test tag', { tag: '@grep-test' }, async ({ page }) => {
    await page.goto('/');
    const title = await page.title();
    expect(title).toBeTruthy();
    console.log('REMOTE: Test with @grep-test tag executed');
  });

  // This test has no tags and should always run unless filtered out
  test('should always run without tags', async ({ page }) => {
    await page.goto('/');
    const title = await page.title();
    expect(title).toBeTruthy();
    console.log('REMOTE: Test without tags executed');
  });
});