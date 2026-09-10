<?php
/**
 * PHPUnit bootstrap: load the WordPress shims, then the plugin (which registers
 * its hooks against those shims).
 *
 * @package TenOutOfTen_No_Notes
 */

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Standing in for the WordPress core constant.
define( 'ABSPATH', dirname( __DIR__ ) . '/' );

require __DIR__ . '/wp-stubs.php';
require dirname( __DIR__ ) . '/ten-out-of-ten-no-notes.php';
require __DIR__ . '/NoNotesTestCase.php';
