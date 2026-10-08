/**
 * Browser end-to-end tests: the block editor's "Add note" affordance.
 *
 * @package TenOutOfTen_No_Notes
 */

const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

// Plugin slugs as @wordpress/e2e-test-utils derives them: paramCase( Plugin Name ).
const NO_NOTES_PLUGIN = '10-10-no-notes';
const KEEP_NOTES_FIXTURE = 'no-notes-e2e-keep-notes';

async function addNoteMenuItem( { editor, page } ) {
	await editor.insertBlock( {
		name: 'core/paragraph',
		attributes: { content: 'A block that could carry a note.' },
	} );
	await editor.showBlockToolbar();
	await editor.clickBlockToolbarButton( 'Options' );

	return page.getByRole( 'menuitem', { name: 'Add note' } );
}

test.describe( '10/10 - No Notes', () => {
	test.beforeAll( async ( { requestUtils } ) => {
		await requestUtils.activatePlugin( NO_NOTES_PLUGIN );
		await requestUtils
			.deactivatePlugin( KEEP_NOTES_FIXTURE )
			.catch( () => {} );
	} );

	test.afterAll( async ( { requestUtils } ) => {
		await requestUtils.deleteAllPosts();
	} );

	test.beforeEach( async ( { admin } ) => {
		await admin.createNewPost();
	} );

	test( '"Add note" is not offered when Notes are disabled everywhere', async ( {
		editor,
		page,
	} ) => {
		const addNote = await addNoteMenuItem( { editor, page } );

		await expect( addNote ).toHaveCount( 0 );

		// Sanity check: the block options menu did open.
		await expect(
			page.getByRole( 'menuitem', { name: /Duplicate/i } )
		).toBeVisible();
	} );

	test.describe( 'when a post type is opted back in via the filter', () => {
		test.beforeAll( async ( { requestUtils } ) => {
			await requestUtils.activatePlugin( KEEP_NOTES_FIXTURE );
		} );

		test.afterAll( async ( { requestUtils } ) => {
			await requestUtils.deactivatePlugin( KEEP_NOTES_FIXTURE );
		} );

		test( '"Add note" is offered again for that post type', async ( {
			editor,
			page,
		} ) => {
			const addNote = await addNoteMenuItem( { editor, page } );

			await expect( addNote ).toBeVisible();
		} );

		test( '"Add note" stays hidden for other post types', async ( {
			admin,
			editor,
			page,
		} ) => {
			// The fixture only opts `post` back in; pages are still covered.
			await admin.createNewPost( { postType: 'page' } );

			// New pages open the "Choose a pattern" modal, which blocks the editor.
			await page
				.getByRole( 'dialog', { name: 'Choose a pattern' } )
				.getByRole( 'button', { name: 'Close' } )
				.click();

			const addNote = await addNoteMenuItem( { editor, page } );

			await expect( addNote ).toHaveCount( 0 );

			// Sanity check: the block options menu did open.
			await expect(
				page.getByRole( 'menuitem', { name: /Duplicate/i } )
			).toBeVisible();
		} );
	} );

	test.describe( 'when not active, notes are offered', () => {
		test.beforeAll( async ( { requestUtils } ) => {
			await requestUtils.deactivatePlugin( NO_NOTES_PLUGIN );
		} );

		test.afterAll( async ( { requestUtils } ) => {
			await requestUtils.activatePlugin( NO_NOTES_PLUGIN );
		} );

		test( '"Add note" is offered for posts', async ( {
			editor,
			page,
		} ) => {
			const addNote = await addNoteMenuItem( { editor, page } );

			await expect( addNote ).toBeVisible();
		} );
	} );

} );
