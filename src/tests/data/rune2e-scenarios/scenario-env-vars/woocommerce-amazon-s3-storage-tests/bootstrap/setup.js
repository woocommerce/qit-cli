import { test, expect } from '@playwright/test';
test('Env Vars', async () => {
 expect(process.env.FOO).toBe('bar');
});