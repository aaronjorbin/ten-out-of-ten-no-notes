/**
 * Playwright global setup: log in once as the admin and persist the session so
 * every spec starts authenticated.
 *
 * @package TenOutOfTen_No_Notes
 */

const { request } = require( '@playwright/test' );
const { RequestUtils } = require( '@wordpress/e2e-test-utils-playwright' );

module.exports = async ( config ) => {
	const { storageState, baseURL } = config.projects[ 0 ].use;
	const storageStatePath =
		typeof storageState === 'string' ? storageState : undefined;

	const requestContext = await request.newContext( { baseURL } );
	const requestUtils = new RequestUtils( requestContext, {
		baseURL,
		storageStatePath,
	} );

	await requestUtils.setupRest();

	await requestContext.dispose();
};
