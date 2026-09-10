<?php
/**
 * Tests that the plugin wires its callbacks onto the right hooks and priorities.
 *
 * @package TenOutOfTen_No_Notes
 */

use PHPUnit\Framework\TestCase;

/**
 * Covers tenoutoften_no_notes_register_hooks().
 */
final class RegisterHooksTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		tenoutoften_test_reset();
	}

	public function test_register_hooks_attaches_both_callbacks(): void {
		tenoutoften_no_notes_register_hooks();

		$hooks = $GLOBALS['__wp_hooks'];
		$this->assertIsArray( $hooks );

		$this->assertSame(
			array(
				array(
					'priority' => 99,
					'cb'       => 'tenoutoften_no_notes_filter_register_args',
				),
			),
			$hooks['register_post_type_args']
		);
		$this->assertSame(
			array(
				array(
					'priority' => PHP_INT_MAX,
					'cb'       => 'tenoutoften_no_notes_sweep_post_types',
				),
			),
			$hooks['init']
		);
	}
}
