import { test, expect } from '@playwright/test';
import fs from 'fs';

test('read global state', async ({ page }) => {
  // Check if global state file exists
  const globalStateFile = '/tmp/qit-global-state.txt';
  
  if (fs.existsSync(globalStateFile)) {
    const content = fs.readFileSync(globalStateFile, 'utf8').trim();
    console.log(`global-setup-test-package: Found global state file with content: ${content}`);
  } else {
    console.log('global-setup-test-package: No global state file found');
  }
  
  // Write a result file for globalTeardown to find
  const resultFile = '/tmp/qit-result-global-setup-test-package.txt';
  fs.writeFileSync(resultFile, 'global-setup-test-package completed');
  console.log('global-setup-test-package: Wrote result file');
  
  // Basic page check
  await page.goto('/');
  // Check that page has a title (works with any test site name)
  const title = await page.title();
  expect(title).toBeTruthy();
  expect(title.length).toBeGreaterThan(0);
  console.log('Page loaded with title:', title);
});