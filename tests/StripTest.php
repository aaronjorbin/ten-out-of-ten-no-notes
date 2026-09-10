<?php
/**
 * Unit tests for tenoutoften_no_notes_strip().
 *
 * @package TenOutOfTen_No_Notes
 */

use PHPUnit\Framework\TestCase;

/**
 * Covers tenoutoften_no_notes_strip().
 */
final class StripTest extends TestCase {

	/**
	 * @dataProvider provide_cases
	 *
	 * @param mixed $input    Editor support value.
	 * @param mixed $expected Expected return.
	 */
	public function test_strip( $input, $expected ): void {
		$this->assertSame( $expected, tenoutoften_no_notes_strip( $input ) );
	}

	/**
	 * @return array<string, array{mixed, mixed}>
	 */
	public function provide_cases(): array {
		return array(
			'bool true untouched (non-array branch)'  => array( true, true ),
			'bool false untouched (non-array branch)' => array( false, false ),
			'null untouched (non-array branch)'       => array( null, null ),

			'numeric feature list untouched'          => array(
				array( 'title', 'editor' ),
				array( 'title', 'editor' ),
			),

			// Flat form — register_post_type() args.
			'flat: notes removed'                     => array(
				array( 'notes' => true ),
				array(),
			),
			'flat: notes removed, siblings kept'      => array(
				array( 'notes' => true, '__experimental' => 1 ),
				array( '__experimental' => 1 ),
			),
			'flat: no notes key, untouched'           => array(
				array( '__experimental' => 1 ),
				array( '__experimental' => 1 ),
			),

			// Nested form — $_wp_post_type_features storage.
			'nested: notes removed'                   => array(
				array( array( 'notes' => true, 'other' => 1 ) ),
				array( array( 'other' => 1 ) ),
			),
			'nested: no notes key, untouched'         => array(
				array( array( 'other' => 1 ) ),
				array( array( 'other' => 1 ) ),
			),
			'nested: notes=false still removed'       => array(
				array( array( 'notes' => false ) ),
				array( array() ),
			),
		);
	}
}
