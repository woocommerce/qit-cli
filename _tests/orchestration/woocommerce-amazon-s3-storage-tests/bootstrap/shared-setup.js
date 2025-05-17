import { test, expect } from '@playwright/test';
import qit from '/qitHelpers';

test('Shared Setup', async ({ page }) => {
    await page.goto('/wp-admin');
    console.log('[Shared Setup JS] Amazon S3 Storage');
    const result = await qit.wp('option get woocommerce-amazon-s3-storage_shared_order', true);
    console.log('Shared Setup Order:', result.output);
    expect(result.stdout.trim()).toBe("first");
});