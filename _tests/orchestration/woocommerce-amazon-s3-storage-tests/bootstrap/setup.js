import { test, expect } from '@playwright/test';
import qit from '/qitHelpers';

test('Isolated Setup', async ({ page }) => {
    await page.goto('/wp-admin');
    console.log('[Isolated Setup JS] Amazon S3 Storage');
    const shared = await qit.wp('option get woocommerce-amazon-s3-storage_shared_order', true);
    expect(shared.stdout.trim()).toBe("first");
    const setup = await qit.wp('option get woocommerce-amazon-s3-storage_isolated_order', true);
    expect(setup.stdout.trim()).toBe("second");

    // Check that Plugin B's isolated option does not exist
    try {
        await qit.wp('option get woocommerce-progressive-discounts_isolated_order', true);
        throw new Error('Expected Plugin B isolated option not to exist, but it does.');
    } catch (e) {
        expect(e.message).toContain("Does it exist?");
    }

    const pluginBShared = await qit.wp('option get woocommerce-progressive-discounts_shared_order', true);
    expect(pluginBShared.stdout.trim()).toBe("first");
});