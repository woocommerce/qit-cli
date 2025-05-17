// /tmp/foo/local/bootstrap/shared-teardown.js
import { test, expect } from '@playwright/test';
import qit from '/qitHelpers';

// shared-teardown.js for Progressive Discounts
test('Shared Teardown', async ({ page }) => {
    await page.goto('/wp-admin');
    console.log('[Shared Teardown JS] Progressive Discounts');

    // Verify shared setup exists
    const shared = await qit.wp('option get woocommerce-progressive-discounts_shared_order', true);
    expect(shared.stdout.trim()).toBe("first");

    // Verify the other plugin's shared option exists (since we run first)
    const otherShared = await qit.wp('option get woocommerce-amazon-s3-storage_shared_order', true);
    expect(otherShared.stdout.trim()).toBe("first");

    // Verify isolated options don't exist (should throw with "Does it exist?")
    try {
        await qit.wp('option get woocommerce-progressive-discounts_isolated_order', true);
        throw new Error("Expected Plugin A isolated option not to exist, but it does.");
    } catch (e) {
        expect(e.message).toContain("Does it exist?");
    }

    try {
        await qit.wp('option get woocommerce-progressive-discounts_teardown_order', true);
        throw new Error("Expected Plugin A teardown option not to exist, but it does.");
    } catch (e) {
        expect(e.message).toContain("Does it exist?");
    }

    // Finally delete shared option
    await qit.wp('option delete woocommerce-progressive-discounts_shared_order');
});