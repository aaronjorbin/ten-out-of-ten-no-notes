<?php
/**
 * Shared base test case: resets the WordPress shims and re-registers the plugin's
 * hooks before every test.
 *
 * @package Tototen_No_Notes
 */

use PHPUnit\Framework\TestCase;

/**
 * Base class for the plugin's hook tests.
 */
abstract class NoNotesTestCase extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		tototen_test_reset();
		tototen_no_notes_register_hooks();
	}

	/**
	 * Return the stored `editor` support for a post type, asserting it is an array.
	 *
	 * @param string $post_type Post type key.
	 * @return array<int|string, mixed>
	 */
	protected function editor_support( string $post_type ): array {
		$supports = get_all_post_type_supports( $post_type );
		$this->assertIsArray( $supports );
		$this->assertArrayHasKey( 'editor', $supports );

		$editor = $supports['editor'];
		$this->assertIsArray( $editor );

		return $editor;
	}

	/**
	 * Return the collected feature-args array (element 0 of the nested support form).
	 *
	 * @param string $post_type Post type key.
	 * @return array<int|string, mixed>
	 */
	protected function editor_args( string $post_type ): array {
		$editor = $this->editor_support( $post_type );
		$this->assertArrayHasKey( 0, $editor );
		$this->assertIsArray( $editor[0] );

		return $editor[0];
	}
}
