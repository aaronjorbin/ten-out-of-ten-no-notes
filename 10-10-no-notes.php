<?php
/**
 * Plugin Name:       10/10 - No Notes
 * Plugin URI:        https://github.com/aaronjorbin/ten-out-of-ten-no-notes
 * Description:        Disables the block editor Notes feature added in WordPress 6.9 by removing the "notes" editor support from every post type. Existing note comments are left untouched.
 * Version:           1.0.0
 * Requires at least: 6.9
 * Requires PHP:      7.4
 * Author:            Aaron Jorbin
 * Author URI:        https://aaron.jorb.in
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       10-10-no-notes
 *
 * @package Tototen_No_Notes
 */

// @codeCoverageIgnoreStart
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// @codeCoverageIgnoreEnd

/**
 * Remove the "notes" flag from an "editor" post type support value.
 *
 * The `editor` support turns up in two shapes:
 *
 *  - Flat, in `register_post_type()` args:      array( 'notes' => true )
 *  - Nested, in the $_wp_post_type_features global: array( 0 => array( 'notes' => true ) )
 *
 * (Core registers `'editor' => array( 'notes' => true )`; `add_post_type_support()`
 * then collects that into `...$args`, producing the nested form when stored.)
 *
 * A plain boolean or a numeric-keyed feature list is returned untouched.
 *
 * @param mixed $editor_support The current `editor` support value.
 * @return mixed The support value with any "notes" flag removed.
 */
function tototen_no_notes_strip( $editor_support ) {
	if ( ! is_array( $editor_support ) ) {
		return $editor_support;
	}

	if ( isset( $editor_support[0] ) && is_array( $editor_support[0] ) ) {
		unset( $editor_support[0]['notes'] );
	} else {
		unset( $editor_support['notes'] );
	}

	return $editor_support;
}

/**
 * Strip the Notes flag as post types are registered.
 *
 * Covers core `post`/`page` (registered via `create_initial_post_types()`) and
 * any custom post type that declares `editor` support at registration.
 *
 * @param array<string, mixed> $args Arguments passed to `register_post_type()`.
 * @return array<string, mixed> Filtered arguments.
 */
function tototen_no_notes_filter_register_args( $args ) {
	if ( isset( $args['supports'] ) && is_array( $args['supports'] ) && isset( $args['supports']['editor'] ) ) {
		$args['supports']['editor'] = tototen_no_notes_strip( $args['supports']['editor'] );
	}

	return $args;
}

/**
 * Sweep every registered post type late on `init`.
 *
 * Catches post types that gain Notes support through an `add_post_type_support()`
 * call made by another plugin or theme after registration.
 *
 * @return void
 */
function tototen_no_notes_sweep_post_types() {
	/**
	 * WordPress' registry of post type feature support.
	 *
	 * @var array<string, array<string, mixed>> $_wp_post_type_features
	 */
	global $_wp_post_type_features;

	if ( empty( $_wp_post_type_features ) || ! is_array( $_wp_post_type_features ) ) {
		return;
	}

	foreach ( $_wp_post_type_features as $type => $features ) {
		if ( ! isset( $features['editor'] ) ) {
			continue;
		}

		$stripped = tototen_no_notes_strip( $features['editor'] );

		if ( $stripped !== $features['editor'] ) {
			/*
			 * Write straight to the global. Re-adding via add_post_type_support()
			 * would wrap the already-collected args array a second time, and
			 * clearing this flag on the global is the entire job of this plugin.
			 */
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Deliberate and load-bearing.
			$_wp_post_type_features[ $type ]['editor'] = $stripped;
		}
	}
}

/**
 * Wire up the plugin's hooks.
 *
 * @return void
 */
function tototen_no_notes_register_hooks() {
	add_filter( 'register_post_type_args', 'tototen_no_notes_filter_register_args', 99 );
	add_action( 'init', 'tototen_no_notes_sweep_post_types', PHP_INT_MAX );
}

// @codeCoverageIgnoreStart
tototen_no_notes_register_hooks();
// @codeCoverageIgnoreEnd
