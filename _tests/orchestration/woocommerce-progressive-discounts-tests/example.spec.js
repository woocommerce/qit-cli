import { test, expect } from '@playwright/test';
import qit from '/qitHelpers';

test('Progressive Discounts Test', async ({ page }) => {
    await qit.loginAsAdmin(page);
    console.log('[Test] Progressive Discounts');
    const shared = await qit.wp('option get woocommerce-progressive-discounts_shared_order', true);
    const isolated = await qit.wp('option get woocommerce-progressive-discounts_isolated_order', true);
    expect(shared.stdout.trim()).toBe("first");
    expect(isolated.stdout.trim()).toBe("second");
});