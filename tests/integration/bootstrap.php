<?php
/**
 * Bootstrap for the WordPress integration test suite.
 *
 * Runs inside the wp-env `tests-cli` container, where the WordPress PHPUnit test
 * library lives at $WP_TESTS_DIR (default /wordpress-phpunit).
 *
 * @package TenOutOfTen_No_Notes
 */

$tenoutoften_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $tenoutoften_tests_dir ) {
	$tenoutoften_tests_dir = '/wordpress-phpunit';
}

$tenoutoften_polyfills = dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
if ( file_exists( $tenoutoften_polyfills ) ) {
	require_once $tenoutoften_polyfills;
}

require_once $tenoutoften_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () {
		require dirname( __DIR__, 2 ) . '/ten-out-of-ten-no-notes.php';
	}
);

require $tenoutoften_tests_dir . '/includes/bootstrap.php';
