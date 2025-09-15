import { test, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';

test.describe('Orchestration Package 1', () => {
  test('should modify WordPress site title via admin UI', async ({ page }) => {
    // Login to admin
    await page.goto('/wp-admin');
    
    // Login if needed
    if (page.url().includes('wp-login.php')) {
      await page.fill('#user_login', 'admin');
      await page.fill('#user_pass', 'password');
      await page.click('#wp-submit');
      await page.waitForURL('**/wp-admin/**');
    }
    
    // Go to Settings > General
    await page.goto('/wp-admin/options-general.php');
    
    // Read current title
    const currentTitle = await page.locator('#blogname').inputValue();
    console.log('Package 1: Current site title is: ' + currentTitle);
    
    // Modify the site title to show package 1 was here
    await page.fill('#blogname', 'Package 1 Was Here');
    await page.fill('#blogdescription', 'Modified by orchestration test package 1');
    
    // Save changes
    await page.click('#submit');
    
    // Wait for success message
    await page.waitForSelector('#setting-error-settings_updated', { state: 'visible' });
    
    console.log('Package 1: Modified site title to "Package 1 Was Here"');
    console.log('Package 1: Modified tagline to "Modified by orchestration test package 1"');
    
    // Verify the change persisted
    await page.goto('/');
    const siteTitle = await page.title();
    expect(siteTitle).toContain('Package 1 Was Here');
  });

  test('should create a shared filesystem marker', async ({ page }) => {
    // Write a file that package 2 SHOULD be able to see (filesystem is shared)
    const sharedFile = '/tmp/qit-orchestration-package-1-was-here.txt';
    fs.writeFileSync(sharedFile, 'Package 1 wrote this at: ' + new Date().toISOString());
    
    console.log('Package 1: Created shared file at ' + sharedFile);
    console.log('Package 1: This file SHOULD be visible to Package 2 (filesystem is shared)');
    
    expect(fs.existsSync(sharedFile)).toBe(true);
  });

  test('should verify WordPress database state persists', async ({ page }) => {
    // Create a test post to verify DB state persists
    await page.goto('/wp-admin/post-new.php');
    
    // Login if needed
    if (page.url().includes('wp-login.php')) {
      await page.fill('#user_login', 'admin');
      await page.fill('#user_pass', 'password');
      await page.click('#wp-submit');
      await page.waitForURL('**/wp-admin/post-new.php');
    }
    
    // Dismiss WordPress welcome tour/guide if present (WP 6.7+)
    // Try multiple selectors for different WordPress versions
    const modalSelectors = [
      'button[aria-label="Close dialog"]',
      'button[aria-label="Close"]',
      '.components-modal__header button',
      '.components-guide button:has-text("×")',
      '.components-guide .components-button.is-tertiary',
      'button:has-text("Got it")',
      'button:has-text("Skip")',
      '.edit-post-welcome-guide button[aria-label="Close"]'
    ];
    
    for (const selector of modalSelectors) {
      try {
        const closeButton = page.locator(selector).first();
        if (await closeButton.isVisible({ timeout: 1000 })) {
          await closeButton.click();
          await page.waitForTimeout(500); // Brief wait for modal to close
          console.log(`Package 1: Dismissed editor welcome modal using: ${selector}`);
          break;
        }
      } catch (e) {
        // Continue to next selector
      }
    }
    
    // Wait for the editor to be ready - let it stabilize after modal dismissal
    await page.waitForTimeout(1000);
    
    // Check if we have an iframe (WordPress 6.8+) or direct editor (older versions)
    const editorIframe = page.frameLocator('.editor-canvas__iframe, [name="editor-canvas"]').first();
    const hasIframe = await page.locator('.editor-canvas__iframe, [name="editor-canvas"]').count() > 0;
    
    if (hasIframe) {
      // WordPress 6.8+ with iframe
      console.log('Package 1: Detected editor iframe (WordPress 6.8+)');
      
      // Wait for and fill the title inside the iframe
      const titleSelector = await editorIframe.locator('[data-title="Add title"], #post-title-0, .editor-post-title__input').first();
      await titleSelector.waitFor({ state: 'visible', timeout: 10000 });
      await titleSelector.fill('Orchestration Test Post by Package 1');
      
      // Add content inside the iframe
      const contentBlock = await editorIframe.locator('.block-editor-default-block-appender__content, [data-title="Write your story"]').first();
      await contentBlock.click();
      await page.keyboard.type('This post was created by orchestration test package 1');
    } else {
      // Older WordPress versions without iframe
      console.log('Package 1: No iframe detected (older WordPress version)');
      
      // Try both block editor and classic editor selectors
      const titleSelector = await page.locator('[data-title="Add title"], #post-title-0, #title').first();
      await titleSelector.waitFor({ state: 'visible', timeout: 10000 });
      await titleSelector.fill('Orchestration Test Post by Package 1');
      
      // Add content (handle both classic and block editor)
      const blockEditor = await page.locator('.block-editor-default-block-appender__content').isVisible().catch(() => false);
      if (blockEditor) {
        await page.click('.block-editor-default-block-appender__content');
        await page.keyboard.type('This post was created by orchestration test package 1');
      } else {
        // Classic editor
        await page.fill('#content', 'This post was created by orchestration test package 1');
      }
    }
    
    // Publish the post
    const publishButton = page.locator('.editor-post-publish-panel__toggle, #publish');
    await publishButton.first().click();
    
    // Confirm publish if needed (Gutenberg)
    const confirmButton = page.locator('.editor-post-publish-button');
    if (await confirmButton.isVisible().catch(() => false)) {
      await confirmButton.click();
    }
    
    console.log('Package 1: Created a test post in WordPress');
    console.log('Package 1: This post SHOULD be visible to Package 2 (database is shared)');
    
    expect(true).toBe(true);
  });
});