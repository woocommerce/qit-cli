// Amazon S3 teardown.js
import { test, expect } from '@playwright/test';
import qit from '/qitHelpers';

test('Isolated Teardown', async ({ page }) => {
    await page.goto('/wp-admin');
    console.log('[Isolated Teardown JS] Amazon S3 Storage');

    // First verify all options exist
    const shared = await qit.wp('option get woocommerce-amazon-s3-storage_shared_order', true);
    const isolated = await qit.wp('option get woocommerce-amazon-s3-storage_isolated_order', true);
    const teardown = await qit.wp('option get woocommerce-amazon-s3-storage_teardown_order', true);
    expect(shared.stdout.trim()).toBe("first");
    expect(isolated.stdout.trim()).toBe("second");
    expect(teardown.stdout.trim()).toBe("third");

    // Then clean up our isolated and teardown options
    await qit.wp('option delete woocommerce-amazon-s3-storage_isolated_order');
    await qit.wp('option delete woocommerce-amazon-s3-storage_teardown_order');
});