import { test, expect } from '@playwright/test';

test('access admin areas that trigger external requests', async ({ page }) => {
	// Login to admin
	await page.goto('/wp-login.php');
	await page.fill('#user_login', 'admin');
	await page.fill('#user_pass', 'password');
	await page.click('#wp-submit');
	await page.waitForURL('**/wp-admin/**');
	
	// Access dashboard (triggers news/events feeds)
	await page.goto('/wp-admin/');
	await page.waitForTimeout(2000);
	
	// Access plugins page (triggers plugin update checks)
	await page.goto('/wp-admin/plugins.php');
	await page.waitForTimeout(1000);
	
	// Access themes page (triggers theme update checks)
	await page.goto('/wp-admin/themes.php');
	await page.waitForTimeout(1000);
	
	// Access updates page (triggers all update checks)
	await page.goto('/wp-admin/update-core.php');
	await page.waitForTimeout(2000);
	
	console.log('Admin areas accessed');
});