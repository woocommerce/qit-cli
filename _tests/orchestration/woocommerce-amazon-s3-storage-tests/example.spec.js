import { test, expect } from '@playwright/test';
import qit from '/qitHelpers';

test('Amazon S3 Storage Test', async ({ page }) => {
    await qit.loginAsAdmin(page);
    console.log('[Test] Amazon S3 Storage');
    const shared = await qit.wp('option get woocommerce-amazon-s3-storage_shared_order', true);
    const isolated = await qit.wp('option get woocommerce-amazon-s3-storage_isolated_order', true);
    expect(shared.stdout.trim()).toBe("first");
    expect(isolated.stdout.trim()).toBe("second");
});