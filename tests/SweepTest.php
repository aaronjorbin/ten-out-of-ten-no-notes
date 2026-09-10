<?php
/**
 * Tests for the late `init` sweep — Notes stripped when another plugin/theme adds
 * `notes` support to an already-registered post type via add_post_type_support().
 *
 * @package Tototen_No_Notes
 */

/**
 * Covers the late init sweep callback.
 */
final class SweepTest extends NoNotesTestCase {

	public function test_notes_added_to_existing_post_type_are_removed(): void {
		// Core-style: editor support with the notes flag, collected into the nested form.
		add_post_type_support( 'post', 'editor', array( 'notes' => true ) );
		$this->assertSame( array( array( 'notes' => true ) ), $this->editor_support( 'post' ) );

		do_action( 'init' );

		$this->assertSame( array( array() ), $this->editor_support( 'post' ) );
		$this->assertTrue( post_type_supports( 'post', 'editor' ), 'editor support itself is kept' );
	}

	public function test_third_party_late_add_is_swept(): void {
		// Another plugin adds notes support on init at an earlier priority.
		add_action(
			'init',
			static function (): void {
				add_post_type_support(
					'page',
					'editor',
					array(
						'notes' => true,
						'keep'  => 1,
					)
				);
			},
			5
		);

		do_action( 'init' );

		$args = $this->editor_args( 'page' );
		$this->assertArrayNotHasKey( 'notes', $args );
		$this->assertSame( 1, $args['keep'], 'other editor args survive' );
	}

	public function test_editor_support_without_notes_is_not_rewritten(): void {
		add_post_type_support( 'cpt', 'editor' ); // Stored as bool true.
		$before = $GLOBALS['_wp_post_type_features'];

		do_action( 'init' );

		$this->assertSame( $before, $GLOBALS['_wp_post_type_features'], 'nothing touched' );
	}

	public function test_post_type_without_editor_support_is_skipped(): void {
		add_post_type_support( 'media_item', 'thumbnail' );

		do_action( 'init' );

		$this->assertSame( array( 'thumbnail' => true ), get_all_post_type_supports( 'media_item' ) );
	}

	public function test_empty_feature_registry_is_a_no_op(): void {
		$GLOBALS['_wp_post_type_features'] = array();

		do_action( 'init' );

		$this->assertSame( array(), $GLOBALS['_wp_post_type_features'] );
	}

	public function test_non_array_feature_registry_is_a_no_op(): void {
		$GLOBALS['_wp_post_type_features'] = 'unexpected';

		do_action( 'init' );

		$this->assertSame( 'unexpected', $GLOBALS['_wp_post_type_features'] );
	}

	public function test_sweep_bails_on_front_end_requests(): void {
		$GLOBALS['__wp_is_admin'] = false;
		$GLOBALS['__wp_is_rest']  = false;
		add_post_type_support( 'post', 'editor', array( 'notes' => true ) );

		do_action( 'init' );

		// Nothing reads Notes support on the front end, so the sweep leaves it be.
		$this->assertSame( array( array( 'notes' => true ) ), $this->editor_support( 'post' ) );
	}

	public function test_sweep_runs_during_rest_requests(): void {
		$GLOBALS['__wp_is_admin'] = false;
		$GLOBALS['__wp_is_rest']  = true;
		add_post_type_support( 'post', 'editor', array( 'notes' => true ) );

		do_action( 'init' );

		$this->assertSame( array( array() ), $this->editor_support( 'post' ) );
	}

	public function test_filter_keeps_notes_for_an_opted_out_post_type(): void {
		add_filter(
			'tototen_no_notes_disabled_for_post_type',
			static function ( bool $disabled, string $post_type ): bool {
				return 'keep_notes' === $post_type ? false : $disabled;
			},
			10,
			2
		);
		add_post_type_support( 'keep_notes', 'editor', array( 'notes' => true ) );
		add_post_type_support( 'strip_notes', 'editor', array( 'notes' => true ) );

		do_action( 'init' );

		$this->assertSame( array( array( 'notes' => true ) ), $this->editor_support( 'keep_notes' ) );
		$this->assertSame( array( array() ), $this->editor_support( 'strip_notes' ) );
	}
}
