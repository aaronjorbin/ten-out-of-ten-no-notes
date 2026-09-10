<?php
/**
 * Tests for the `register_post_type_args` filter — Notes stripped as a post type
 * is registered.
 *
 * @package Tototen_No_Notes
 */

/**
 * Covers the register_post_type_args filter callback.
 */
final class RegisterArgsFilterTest extends NoNotesTestCase {

	public function test_notes_stripped_from_new_post_type_with_editor_notes(): void {
		register_post_type(
			'book',
			array( 'supports' => array( 'title', 'editor' => array( 'notes' => true ) ) )
		);

		$this->assertTrue( post_type_supports( 'book', 'editor' ), 'editor support kept' );
		$this->assertFalse( $this->has_notes( $this->editor_support( 'book' ) ), 'notes flag gone' );
		$this->assertTrue( post_type_supports( 'book', 'title' ), 'unrelated support untouched' );
	}

	public function test_sibling_editor_args_are_preserved(): void {
		register_post_type(
			'lesson',
			array( 'supports' => array( 'editor' => array( 'notes' => true, '__experimental' => 'x' ) ) )
		);

		$args = $this->editor_args( 'lesson' );

		$this->assertArrayNotHasKey( 'notes', $args );
		$this->assertSame( 'x', $args['__experimental'] );
	}

	public function test_post_type_without_editor_support_is_untouched(): void {
		register_post_type( 'glossary', array( 'supports' => array( 'title', 'thumbnail' ) ) );

		$this->assertSame(
			array(
				'title'     => true,
				'thumbnail' => true,
			),
			get_all_post_type_supports( 'glossary' )
		);
	}

	public function test_editor_support_without_notes_flag_is_left_alone(): void {
		register_post_type(
			'note_free',
			array( 'supports' => array( 'editor' => array( '__experimental' => 1 ) ) )
		);

		$this->assertSame( array( '__experimental' => 1 ), $this->editor_args( 'note_free' ) );
	}

	public function test_plain_editor_support_string_still_works(): void {
		register_post_type( 'plain', array( 'supports' => array( 'editor' ) ) );

		$this->assertTrue( post_type_supports( 'plain', 'editor' ) );
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

		register_post_type( 'keep_notes', array( 'supports' => array( 'editor' => array( 'notes' => true ) ) ) );
		register_post_type( 'strip_notes', array( 'supports' => array( 'editor' => array( 'notes' => true ) ) ) );

		$this->assertTrue( $this->has_notes( $this->editor_support( 'keep_notes' ) ), 'opted-out type keeps notes' );
		$this->assertFalse( $this->has_notes( $this->editor_support( 'strip_notes' ) ), 'other types still stripped' );
	}

	/**
	 * @param array<int|string, mixed> $editor_support Stored editor support value.
	 */
	private function has_notes( array $editor_support ): bool {
		if ( isset( $editor_support[0] ) && is_array( $editor_support[0] ) ) {
			return array_key_exists( 'notes', $editor_support[0] );
		}

		return array_key_exists( 'notes', $editor_support );
	}
}
