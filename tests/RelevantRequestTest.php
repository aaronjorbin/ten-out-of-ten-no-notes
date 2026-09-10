<?php
/**
 * Tests for tenoutoften_no_notes_is_relevant_request() — the sweep's early-exit gate.
 *
 * @package TenOutOfTen_No_Notes
 */

/**
 * Covers tenoutoften_no_notes_is_relevant_request().
 */
final class RelevantRequestTest extends NoNotesTestCase {

	public function test_relevant_in_the_admin(): void {
		$GLOBALS['__wp_is_admin'] = true;
		$GLOBALS['__wp_is_rest']  = false;

		$this->assertTrue( tenoutoften_no_notes_is_relevant_request() );
	}

	public function test_relevant_during_a_rest_request(): void {
		$GLOBALS['__wp_is_admin'] = false;
		$GLOBALS['__wp_is_rest']  = true;

		$this->assertTrue( tenoutoften_no_notes_is_relevant_request() );
	}

	public function test_not_relevant_on_a_front_end_request(): void {
		$GLOBALS['__wp_is_admin'] = false;
		$GLOBALS['__wp_is_rest']  = false;

		$this->assertFalse( tenoutoften_no_notes_is_relevant_request() );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_relevant_under_wp_cli(): void {
		$GLOBALS['__wp_is_admin'] = false;
		$GLOBALS['__wp_is_rest']  = false;
		define( 'WP_CLI', true );

		$this->assertTrue( tenoutoften_no_notes_is_relevant_request() );
	}
}
