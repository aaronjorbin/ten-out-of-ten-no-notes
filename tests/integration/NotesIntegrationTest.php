<?php
/**
 * End-to-end tests against a real WordPress install (via wp-env).
 *
 * These exercise the actual `register_post_type`, `$_wp_post_type_features`, and
 * REST comments controller code paths, so a change to how core gates Notes would
 * break them.
 *
 * @package TenOutOfTen_No_Notes
 */

/**
 * @group integration
 */
class NotesIntegrationTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();

		// A fresh REST server for the endpoint tests.
		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init' );
	}

	public function tear_down() {
		global $wp_rest_server;
		$wp_rest_server = null;

		parent::tear_down();
	}

	/**
	 * Mirror of WP_REST_Comments_Controller::check_post_type_supports_notes().
	 */
	private function post_type_allows_notes( string $post_type ): bool {
		$supports = get_all_post_type_supports( $post_type );

		if ( empty( $supports['editor'] ) || ! is_array( $supports['editor'] ) ) {
			return false;
		}

		foreach ( $supports['editor'] as $item ) {
			if ( ! empty( $item['notes'] ) ) {
				return true;
			}
		}

		return false;
	}

	public function test_core_post_and_page_do_not_allow_notes() {
		$this->assertFalse( $this->post_type_allows_notes( 'post' ) );
		$this->assertFalse( $this->post_type_allows_notes( 'page' ) );
	}

	public function test_editor_support_itself_is_retained() {
		$this->assertTrue( post_type_supports( 'post', 'editor' ) );
		$this->assertTrue( post_type_supports( 'page', 'editor' ) );
	}

	public function test_custom_post_type_registered_with_notes_is_stripped() {
		register_post_type(
			'tenoutoften_book',
			array(
				'public'   => true,
				'supports' => array( 'title', 'editor' => array( 'notes' => true ) ),
			)
		);

		$this->assertFalse( $this->post_type_allows_notes( 'tenoutoften_book' ) );

		unregister_post_type( 'tenoutoften_book' );
	}

	public function test_filter_opts_a_post_type_back_in() {
		add_filter(
			'tenoutoften_no_notes_disabled_for_post_type',
			static function ( $disabled, $post_type ) {
				return 'tenoutoften_kept' === $post_type ? false : $disabled;
			},
			10,
			2
		);

		register_post_type(
			'tenoutoften_kept',
			array(
				'public'   => true,
				'supports' => array( 'editor' => array( 'notes' => true ) ),
			)
		);
		register_post_type(
			'tenoutoften_stripped',
			array(
				'public'   => true,
				'supports' => array( 'editor' => array( 'notes' => true ) ),
			)
		);

		$this->assertTrue( $this->post_type_allows_notes( 'tenoutoften_kept' ), 'opted-out type keeps Notes' );
		$this->assertFalse( $this->post_type_allows_notes( 'tenoutoften_stripped' ), 'other types still stripped' );

		unregister_post_type( 'tenoutoften_kept' );
		unregister_post_type( 'tenoutoften_stripped' );
	}

	public function test_late_add_post_type_support_is_swept_in_admin_context() {
		set_current_screen( 'edit-post' );
		$this->assertTrue( is_admin() );

		add_post_type_support( 'page', 'editor', array( 'notes' => true ) );
		$this->assertTrue( $this->post_type_allows_notes( 'page' ), 'notes re-added' );

		do_action( 'init' );

		$this->assertFalse( $this->post_type_allows_notes( 'page' ), 'sweep removed them again' );

		set_current_screen( 'front' );
	}

	public function test_existing_note_comments_are_not_deleted() {
		$post_id = self::factory()->post->create();
		$note_id = wp_insert_comment(
			array(
				'comment_post_ID'  => $post_id,
				'comment_content'  => 'a pre-existing note',
				'comment_type'     => 'note',
				'comment_approved' => 0,
			)
		);

		$this->assertIsInt( $note_id );

		do_action( 'init' );

		$note = get_comment( $note_id );
		$this->assertInstanceOf( WP_Comment::class, $note );
		$this->assertSame( 'note', $note->comment_type );
		$this->assertSame( 'a pre-existing note', $note->comment_content );
	}

	public function test_rest_rejects_creating_a_note_on_an_unsupported_post_type() {
		$admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin );
		$post_id = self::factory()->post->create();

		$request = new WP_REST_Request( 'POST', '/wp/v2/comments' );
		$request->set_param( 'post', $post_id );
		$request->set_param( 'content', 'a note via REST' );
		$request->set_param( 'type', 'note' );

		$response = rest_get_server()->dispatch( $request );

		$this->assertSame( 403, $response->get_status() );
		$this->assertSame( 'rest_comment_not_supported_post_type', $response->get_data()['code'] ?? '' );
	}
}
