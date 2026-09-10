<?php
/**
 * Bootstrap for the WordPress integration test suite.
 *
 * Runs inside the wp-env `tests-cli` container, where the WordPress PHPUnit test
 * library lives at $WP_TESTS_DIR (default /wordpress-phpunit).
 *
 * @package Tototen_No_Notes
 */

$tototen_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $tototen_tests_dir ) {
	$tototen_tests_dir = '/wordpress-phpunit';
}

$tototen_polyfills = dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
if ( file_exists( $tototen_polyfills ) ) {
	require_once $tototen_polyfills;
}

require_once $tototen_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () {
		require dirname( __DIR__, 2 ) . '/10-10-no-notes.php';
	}
);

require $tototen_tests_dir . '/includes/bootstrap.php';
