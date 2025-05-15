// /tmp/foo/local/bootstrap/shared-teardown.js
import {test, expect} from '@playwright/test';
import qit from '/qitHelpers';

// shared-teardown.js for Amazon S3
test('Shared Teardown', async ({page}) => {
    await page.goto('/wp-admin');
    console.log('[Shared Teardown JS] Amazon S3 Storage');

    // Verify shared setup exists
    const shared = await qit.wp('option get woocommerce-amazon-s3-storage_shared_order', true);
    expect(shared.stdout.trim()).toBe("first");

    // Verify the other plugin's shared option does NOT exist (since we run last)
    try {
        await qit.wp('option get woocommerce-progressive-discounts_shared_order', true);
        throw new Error("Expected other plugin's shared option not to exist, but it does.");
    } catch (e) {
        expect(e.message).toContain("Does it exist?");
    }

    // Verify isolated options don't exist
    try {
        await qit.wp('option get woocommerce-amazon-s3-storage_isolated_order', true);
        throw new Error("Expected isolated option not to exist, but it does.");
    } catch (e) {
        expect(e.message).toContain("Does it exist?");
    }

    try {
        await qit.wp('option get woocommerce-amazon-s3-storage_teardown_order', true);
        throw new Error("Expected teardown option not to exist, but it does.");
    } catch (e) {
        expect(e.message).toContain("Does it exist?");
    }

    // Finally delete shared option
    await qit.wp('option delete woocommerce-amazon-s3-storage_shared_order');
});