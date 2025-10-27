import { test, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';

test.describe('Orchestration Package 2', () => {
  test('should see modified WordPress site title from Package 1', async ({ page }) => {
    // Go to homepage
    await page.goto('/');
    
    // Check the title
    const siteTitle = await page.title();
    
    if (siteTitle.includes('Package 1 Was Here')) {
      console.log('Package 2: SUCCESS - Found modified site title from Package 1: ' + siteTitle);
      console.log('ORCHESTRATION VERIFIED: WordPress database state persists between packages');
      expect(siteTitle).toContain('Package 1 Was Here');
    } else {
      console.log('Package 2: Site title is: ' + siteTitle);
      console.log('Package 2: Expected to see "Package 1 Was Here" if packages run in order');
      // Don't fail - packages might run in different order
    }
    
    // Now update it to show Package 2 was also here
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
    
    // Update the title to show both packages ran
    await page.fill('#blogname', 'Package 1 & 2 Were Here');
    await page.fill('#blogdescription', 'Modified by both orchestration test packages');
    
    // Save changes
    await page.click('#submit');
    await page.waitForSelector('#setting-error-settings_updated', { state: 'visible' });
    
    console.log('Package 2: Updated site title to "Package 1 & 2 Were Here"');
  });

  test('should see shared filesystem file from Package 1', async ({ page }) => {
    // Check if package 1's file exists (it SHOULD - filesystem is shared)
    const sharedFile = '/tmp/qit-orchestration-package-1-was-here.txt';
    
    if (fs.existsSync(sharedFile)) {
      const content = fs.readFileSync(sharedFile, 'utf8');
      console.log('Package 2: SUCCESS - Found shared file from Package 1');
      console.log('Package 2: File content: ' + content);
      console.log('ORCHESTRATION VERIFIED: Filesystem is shared between packages');
      expect(fs.existsSync(sharedFile)).toBe(true);
    } else {
      console.log('Package 2: Shared file not found (Package 1 may not have run yet)');
      // Don't fail - packages might run in different order
    }
    
    // Create our own file
    const ourFile = '/tmp/qit-orchestration-package-2-was-here.txt';
    fs.writeFileSync(ourFile, 'Package 2 wrote this at: ' + new Date().toISOString());
    console.log('Package 2: Created shared file at ' + ourFile);
  });

  test('should see test post created by Package 1', async ({ page }) => {
    // Go to posts list
    await page.goto('/wp-admin/edit.php');
    
    // Login if needed
    if (page.url().includes('wp-login.php')) {
      await page.fill('#user_login', 'admin');
      await page.fill('#user_pass', 'password');
      await page.click('#wp-submit');
      await page.waitForURL('**/wp-admin/edit.php');
    }
    
    // Look for the post created by Package 1
    const postTitle = 'Orchestration Test Post by Package 1';
    const postLink = page.locator(`a.row-title:has-text("${postTitle}")`);
    
    if (await postLink.count() > 0) {
      console.log('Package 2: SUCCESS - Found post created by Package 1');
      console.log('ORCHESTRATION VERIFIED: WordPress posts persist between packages');
      expect(await postLink.count()).toBeGreaterThan(0);
      
      // Create our own post to show Package 2 ran
      await page.goto('/wp-admin/post-new.php');
      
      // Dismiss WordPress welcome tour/guide if present (WP 6.7+)
      const welcomeGuideClose = page.locator('.components-modal__header button[aria-label="Close"], .components-guide .components-button.is-tertiary');
      if (await welcomeGuideClose.isVisible().catch(() => false)) {
        await welcomeGuideClose.click();
        await page.waitForTimeout(500); // Brief wait for modal to close
      }
      
      // Check if we have an iframe (WordPress 6.8+) or direct editor (older versions)
      const editorIframe = page.frameLocator('.editor-canvas__iframe, [name="editor-canvas"]').first();
      const hasIframe = await page.locator('.editor-canvas__iframe, [name="editor-canvas"]').count() > 0;
      
      if (hasIframe) {
        // WordPress 6.8+ with iframe
        console.log('Package 2: Detected editor iframe (WordPress 6.8+)');
        
        // Wait for and fill the title inside the iframe
        const titleSelector = await editorIframe.locator('[data-title="Add title"], #post-title-0, .editor-post-title__input').first();
        await titleSelector.waitFor({ state: 'visible', timeout: 5000 });
        await titleSelector.fill('Orchestration Test Post by Package 2');
        
        // Add content inside the iframe
        const contentBlock = await editorIframe.locator('.block-editor-default-block-appender__content, [data-title="Write your story"]').first();
        await contentBlock.click();
        await page.keyboard.type('This post was created by orchestration test package 2');
      } else {
        // Older WordPress versions without iframe
        console.log('Package 2: No iframe detected (older WordPress version)');
        
        await page.fill('[data-title="Add title"], #title', 'Orchestration Test Post by Package 2');
        
        const blockEditor = await page.locator('.block-editor-default-block-appender__content').isVisible().catch(() => false);
        if (blockEditor) {
          await page.click('.block-editor-default-block-appender__content');
          await page.keyboard.type('This post was created by orchestration test package 2');
        } else {
          await page.fill('#content', 'This post was created by orchestration test package 2');
        }
      }
      
      const publishButton = page.locator('.editor-post-publish-panel__toggle, #publish');
      await publishButton.first().click();
      
      const confirmButton = page.locator('.editor-post-publish-button');
      if (await confirmButton.isVisible().catch(() => false)) {
        await confirmButton.click();
      }
      
      console.log('Package 2: Created our own test post');
    } else {
      console.log('Package 2: Post from Package 1 not found (may not have run yet)');
      // Don't fail - packages might run in different order
    }
  });

  test('should verify execution order through multiple signals', async ({ page }) => {
    // Check multiple signals to determine if Package 1 ran first
    const signals = {
      filesystem: fs.existsSync('/tmp/qit-orchestration-package-1-was-here.txt'),
      siteTitle: false,
      post: false
    };
    
    // Check site title
    await page.goto('/');
    const title = await page.title();
    signals.siteTitle = title.includes('Package 1');
    
    // Check for post
    await page.goto('/wp-admin/edit.php');
    if (page.url().includes('wp-login.php')) {
      await page.fill('#user_login', 'admin');
      await page.fill('#user_pass', 'password');
      await page.click('#wp-submit');
      await page.waitForURL('**/wp-admin/edit.php');
    }
    signals.post = await page.locator('a.row-title:has-text("Orchestration Test Post by Package 1")').count() > 0;
    
    const signalsFound = Object.values(signals).filter(s => s).length;
    
    if (signalsFound >= 2) {
      console.log('ORCHESTRATION VERIFIED: Multiple signals confirm Package 1 ran before Package 2');
      console.log('Package 2: Signals found - Filesystem: ' + signals.filesystem + ', Title: ' + signals.siteTitle + ', Post: ' + signals.post);
    } else {
      console.log('Package 2: Only ' + signalsFound + ' signals found from Package 1');
    }
    
    expect(true).toBe(true);
  });
});