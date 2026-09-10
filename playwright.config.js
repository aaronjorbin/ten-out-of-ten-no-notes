/**
 * Playwright configuration for the browser end-to-end tests.
 *
 * Drives a real WordPress block editor served by wp-env. Start the environment
 * with `npm run env:start` before running `npm run test:e2e`.
 */

const { defineConfig, devices } = require( '@playwright/test' );

const STORAGE_STATE_PATH =
	process.env.STORAGE_STATE_PATH ||
	require( 'path' ).join( process.cwd(), 'artifacts/storage-states/admin.json' );

process.env.WP_BASE_URL = process.env.WP_BASE_URL || 'http://localhost:60988';
process.env.STORAGE_STATE_PATH = STORAGE_STATE_PATH;

module.exports = defineConfig( {
	testDir: './tests/e2e',
	outputDir: './artifacts/test-results',
	fullyParallel: false,
	workers: 1,
	retries: process.env.CI ? 2 : 0,
	timeout: 120_000,
	forbidOnly: !! process.env.CI,
	reporter: process.env.CI ? [ [ 'github' ], [ 'list' ] ] : 'list',
	globalSetup: require.resolve( './tests/e2e/global-setup.js' ),
	use: {
		baseURL: process.env.WP_BASE_URL,
		headless: true,
		viewport: { width: 1280, height: 800 },
		ignoreHTTPSErrors: true,
		locale: 'en-US',
		contextOptions: { reducedMotion: 'reduce', strictSelectors: true },
		storageState: STORAGE_STATE_PATH,
		actionTimeout: 15_000,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
		video: 'on-first-retry',
	},
	projects: [
		{ name: 'chromium', use: { ...devices[ 'Desktop Chrome' ] } },
	],
} );
