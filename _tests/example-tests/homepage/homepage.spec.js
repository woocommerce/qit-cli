const { test, expect, request } = require( '@playwright/test' );

// Write a basic test to visit the homepage.
test( 'visit homepage', async ( { page, baseURL } ) => {
    await page.goto( '/' );
    await expect(page).toHaveScreenshot('example.png')
} );