const { test: base, expect, request } = require( '@playwright/test' );
const { AssemblerPage } = require( './assembler.page' );
const { CustomizeStorePage } = require( '../customize-store.page' );

const { activateTheme, DEFAULT_THEME } = require( '../../../utils/themes' );
const { setOption } = require( '../../../utils/options' );

const test = base.extend( {
	assemblerPageObject: async ( { page }, use ) => {
		const assemblerPageObject = new AssemblerPage( { page } );
		await use( assemblerPageObject );
	},
	customizeStorePageObject: async ( {}, use ) => {
		const assemblerPageObject = new CustomizeStorePage( { request } );
		await use( assemblerPageObject );
	},
} );

const colorPalette = {
	'Blueberry Sorbet': {
		button: {
			background: 'rgb(189, 64, 137)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(255, 255, 255)', 'rgb(32, 56, 182)' ],
		},
		header: {
			color: [ 'rgb(255, 255, 255)', 'rgb(32, 56, 182)' ],
		},
	},
	'Ancient Bronze': {
		button: {
			background: 'rgb(140, 131, 105)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(50, 56, 86)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(50, 56, 86)', 'rgb(255, 255, 255)' ],
		},
	},
	'Crimson Tide': {
		button: {
			background: 'rgb(236, 94, 63)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(16, 19, 23)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(16, 19, 23)', 'rgb(255, 255, 255)' ],
		},
	},
	'Purple Twilight': {
		button: {
			background: 'rgb(106, 94, 183)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(9, 9, 9)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(9, 9, 9)', 'rgb(255, 255, 255)' ],
		},
	},
	'Green Thumb': {
		button: {
			background: 'rgb(75, 123, 77)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(22, 74, 65)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(22, 74, 65)', 'rgb(255, 255, 255)' ],
		},
	},
	'Golden Haze': {
		button: {
			background: 'rgb(235, 181, 79)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(81, 81, 81)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(81, 81, 81)', 'rgb(255, 255, 255)' ],
		},
	},
	'Golden Indigo': {
		button: {
			background: 'rgb(192, 159, 80)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(64, 90, 167)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(64, 90, 167)', 'rgb(255, 255, 255)' ],
		},
	},
	'Arctic Dawn': {
		button: {
			background: 'rgb(221, 48, 29)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(13, 18, 99)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(13, 18, 99)', 'rgb(255, 255, 255)' ],
		},
	},
	'Raspberry Chocolate': {
		button: {
			background: 'rgb(214, 77, 104)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(36, 29, 26)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(36, 29, 26)', 'rgb(255, 255, 255)' ],
		},
	},
	Canary: {
		button: {
			background: 'rgb(102, 102, 102)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(15, 15, 5)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(15, 15, 5)', 'rgb(255, 255, 255)' ],
		},
	},
	Ice: {
		button: {
			background: 'rgb(18, 18, 63)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(18, 18, 63)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(18, 18, 63)', 'rgb(255, 255, 255)' ],
		},
	},
	'Rustic Rosewood': {
		button: {
			background: 'rgb(238, 121, 124)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(9, 9, 9)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(9, 9, 9)', 'rgb(255, 255, 255)' ],
		},
	},
	'Cinnamon Latte': {
		button: {
			background: 'rgb(188, 128, 52)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(9, 9, 9)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(9, 9, 9)', 'rgb(255, 255, 255)' ],
		},
	},
	Lightning: {
		button: {
			background: 'rgb(254, 254, 254)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(235, 255, 210)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(235, 255, 210)', 'rgb(255, 255, 255)' ],
		},
	},
	'Aquamarine Night': {
		button: {
			background: 'rgb(86, 251, 185)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(255, 255, 255)' ],
		},
	},
	Charcoal: {
		button: {
			background: 'rgb(239, 239, 239)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(219, 219, 219)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(219, 219, 219)', 'rgb(255, 255, 255)' ],
		},
	},
	Slate: {
		button: {
			background: 'rgb(255, 223, 109)',
			color: 'rgb(253, 251, 239)',
		},
		paragraph: {
			color: [ 'rgb(239, 242, 249)', 'rgb(255, 255, 255)' ],
		},
		header: {
			color: [ 'rgb(239, 242, 249)', 'rgb(255, 255, 255)' ],
		},
	},
};

test.describe( 'Assembler -> Color Pickers', () => {
	test.use( { storageState: process.env.ADMINSTATE } );

	test.beforeAll( async ( { baseURL } ) => {
		try {
			// In some environments the tour blocks clicking other elements.
			await setOption(
				request,
				baseURL,
				'woocommerce_customize_store_onboarding_tour_hidden',
				'yes'
			);
		} catch ( error ) {
			console.log( 'Store completed option not updated' );
		}
	} );

	test.afterAll( async ( { baseURL, customizeStorePageObject } ) => {
		try {
			// In some environments the tour blocks clicking other elements.
			await setOption(
				request,
				baseURL,
				'woocommerce_customize_store_onboarding_tour_hidden',
				'no'
			);
			await setOption(
				request,
				baseURL,
				'woocommerce_admin_customize_store_completed',
				'no'
			);

			// Reset theme back to default.
			await activateTheme( DEFAULT_THEME );
			await customizeStorePageObject.resetCustomizeStoreChanges(
				baseURL
			);
		} catch ( error ) {
			console.log( 'Store completed option not updated' );
		}
	} );

	test.beforeEach( async ( { baseURL, assemblerPageObject } ) => {
		await assemblerPageObject.setupSite( baseURL );
		await assemblerPageObject.waitForLoadingScreenFinish();
		const assembler = await assemblerPageObject.getAssembler();
		await assembler.getByText( 'Choose your color palette' ).click();
	} );

	test( 'Color pickers should be displayed', async ( {
		assemblerPageObject,
	} ) => {
		const assembler = await assemblerPageObject.getAssembler();

		const colorPickers = assembler.locator(
			'.woocommerce-customize-store_global-styles-variations_item'
		);
		await expect( colorPickers ).toHaveCount( 18 );
	} );

	for ( const [ colorPaletteName, colors ] of Object.entries(
		colorPalette
	) ) {
		test( `Color palette ${ colorPaletteName } should be applied`, async ( {
			assemblerPageObject,
			page,
		} ) => {
			const assembler = await assemblerPageObject.getAssembler();
			const editor = await assemblerPageObject.getEditor();

			const colorPicker = assembler.getByLabel( colorPaletteName );

			await colorPicker.click();

			const saveButton = assembler.getByText( 'Save' );

			const waitResponse = page.waitForResponse(
				( response ) =>
					response.url().includes( 'wp-json/wp/v2/global-styles' ) &&
					response.status() === 200
			);

			await saveButton.click();

			await waitResponse;

			const buttons = await editor
				.locator( '.wp-block-button > .wp-block-button__link' )
				.evaluateAll( ( elements ) =>
					elements.map( ( element ) => {
						const style = window.getComputedStyle( element );
						return {
							background: style.backgroundColor,
							color: style.color,
						};
					} )
				);

			const paragraphs = await editor
				.locator(
					'p.wp-block.wp-block-paragraph:not([aria-label="Empty block; start writing or type forward slash to choose a block"])'
				)
				.evaluateAll( ( elements ) =>
					elements.map( ( element ) => {
						const style = window.getComputedStyle( element );
						return {
							background: style.backgroundColor,
							color: style.color,
						};
					} )
				);

			const headers = await editor
				.locator( 'h1, h2, h3, h4, h5, h6' )
				.evaluateAll( ( elements ) =>
					elements.map( ( element ) => {
						const style = window.getComputedStyle( element );
						return {
							background: style.backgroundColor,
							color: style.color,
						};
					} )
				);

			for ( const element of buttons ) {
				await expect( element.background ).toEqual(
					colors.button.background
				);
			}

			for ( const element of paragraphs ) {
				expect( colors.paragraph.color.includes( element.color ) ).toBe(
					true
				);
			}

			for ( const element of headers ) {
				expect( colors.header.color.includes( element.color ) ).toBe(
					true
				);
			}
		} );
	}

	test( 'Color picker should be focused when a color is picked', async ( {
		assemblerPageObject,
	} ) => {
		const assembler = await assemblerPageObject.getAssembler();
		const colorPicker = assembler
			.locator(
				'.woocommerce-customize-store_global-styles-variations_item'
			)
			.first();

		await colorPicker.click();
		await expect( colorPicker ).toHaveClass( /is-active/ );
	} );

	test( 'Picking a color should activate the save button', async ( {
		assemblerPageObject,
	} ) => {
		const assembler = await assemblerPageObject.getAssembler();
		const colorPicker = assembler
			.locator(
				'.woocommerce-customize-store_global-styles-variations_item'
			)
			.nth( 2 );

		await colorPicker.click();

		const saveButton = assembler.getByText( 'Save' );

		await expect( saveButton ).toBeEnabled();
	} );

	test( 'The Done button should be visible after clicking save', async ( {
		assemblerPageObject,
		page,
	} ) => {
		const assembler = await assemblerPageObject.getAssembler();
		const colorPicker = assembler
			.locator(
				'.woocommerce-customize-store_global-styles-variations_item'
			)
			.nth( 2 );

		await colorPicker.click();

		const saveButton = assembler.getByText( 'Save' );

		const waitResponse = page.waitForResponse(
			( response ) =>
				response.url().includes( 'wp-json/wp/v2/global-styles' ) &&
				response.status() === 200
		);

		await saveButton.click();

		await waitResponse;

		await expect( assembler.getByText( 'Done' ) ).toBeEnabled();
	} );

	test( 'Selected color palette should be applied on the frontend', async ( {
		assemblerPageObject,
		page,
		baseURL,
	}, testInfo ) => {
		testInfo.snapshotSuffix = '';
		const assembler = await assemblerPageObject.getAssembler();
		const colorPicker = assembler
			.locator(
				'.woocommerce-customize-store_global-styles-variations_item'
			)
			.last();

		await colorPicker.click();

		const saveButton = assembler.getByText( 'Save' );

		const waitResponse = page.waitForResponse(
			( response ) =>
				response.url().includes( 'wp-json/wp/v2/global-styles' ) &&
				response.status() === 200
		);

		await saveButton.click();

		await waitResponse;

		await page.goto( baseURL );

		const paragraphs = await page
			.locator(
				'p.wp-block.wp-block-paragraph:not([aria-label="Empty block; start writing or type forward slash to choose a block"])'
			)
			.evaluateAll( ( elements ) =>
				elements.map( ( element ) => {
					const style = window.getComputedStyle( element );
					return {
						background: style.backgroundColor,
						color: style.color,
					};
				} )
			);

		const buttons = await page
			.locator( '.wp-block-button > .wp-block-button__link' )
			.evaluateAll( ( elements ) =>
				elements.map( ( element ) => {
					const style = window.getComputedStyle( element );
					return {
						background: style.backgroundColor,
						color: style.color,
					};
				} )
			);

		const headers = await page
			.locator( 'h1, h2, h3, h4, h5, h6' )
			.evaluateAll( ( elements ) =>
				elements.map( ( element ) => {
					const style = window.getComputedStyle( element );
					return {
						background: style.backgroundColor,
						color: style.color,
					};
				} )
			);

		for ( const element of buttons ) {
			await expect( element.background ).toEqual(
				colorPalette.Slate.button.background
			);
		}

		for ( const element of paragraphs ) {
			expect(
				colorPalette.Slate.paragraph.color.includes( element.color )
			).toBe( true );
		}

		for ( const element of headers ) {
			expect(
				colorPalette.Slate.header.color.includes( element.color )
			).toBe( true );
		}
	} );

	test( 'Create "your own" pickers should be visible', async ( {
		assemblerPageObject,
	}, testInfo ) => {
		testInfo.snapshotSuffix = '';
		const assembler = await assemblerPageObject.getAssembler();
		const colorPicker = assembler.getByText( 'Create your own' );

		await colorPicker.click();

		const mapTypeFeatures = {
			background: [ 'solid', 'gradient' ],
			text: [],
			heading: [ 'text', 'background', 'gradient' ],
			button: [ 'text', 'background', 'gradient' ],
			link: [ 'default', 'hover' ],
			captions: [],
		};

		const mapFeatureSelectors = {
			solid: '.components-color-palette__custom-color-button',
			text: '.components-color-palette__custom-color-button',
			background: '.components-color-palette__custom-color-button',
			default: '.components-color-palette__custom-color-button',
			hover: '.components-color-palette__custom-color-button',
			gradient:
				'.components-custom-gradient-picker__gradient-bar-background',
		};

		for ( const type of Object.keys( mapTypeFeatures ) ) {
			await assembler
				.locator(
					'.woocommerce-customize-store__color-panel-container'
				)
				.getByText( type )
				.click();

			for ( const feature of mapTypeFeatures[ type ] ) {
				const container = assembler.locator(
					'.block-editor-panel-color-gradient-settings__dropdown-content'
				);
				await container
					.getByRole( 'tab', {
						name: feature,
					} )
					.click();

				const selector = mapFeatureSelectors[ feature ];
				const featureSelector = container.locator( selector );

				await expect( featureSelector ).toBeVisible();
			}
		}
	} );
} );
