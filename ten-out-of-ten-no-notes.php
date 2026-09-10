<?php
/**
 * Plugin Name:       10/10 - No Notes
 * Plugin URI:        https://github.com/aaronjorbin/ten-out-of-ten-no-notes
 * Description:        Disables the ability to add Notes, the block editor feature added in WordPress 6.9, by removing the "notes" editor support from every post type. Existing note comments are left untouched.
 * Version:           1.1.0
 * Requires at least: 6.9
 * Requires PHP:      7.4
 * Author:            Aaron Jorbin
 * Author URI:        https://aaron.jorb.in
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tenoutoften
 *
 * @package TenOutOfTen_No_Notes
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
function tenoutoften_no_notes_strip( $editor_support ) {
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
 * Whether Notes support should be stripped from a given post type.
 *
 * @param string $post_type Post type key.
 * @return bool
 */
function tenoutoften_no_notes_disabled_for( $post_type ) {
	/**
	 * Filters whether "10/10 - No Notes" strips Notes support from a post type.
	 *
	 * Return false for a post type to leave Notes switched on there. Do that for
	 * enough post types and you are really running "9/10 - Some Notes", which is
	 * a different (also perfectly respectable) plugin.
	 *
	 * @since 1.1.0
	 *
	 * @param bool   $disabled  Whether to strip "notes" editor support. Default true.
	 * @param string $post_type Post type key.
	 */
	return (bool) apply_filters( 'tenoutoften_no_notes_disabled_for_post_type', true, $post_type );
}

/**
 * Strip the Notes flag as post types are registered.
 *
 * Covers core `post`/`page` (registered via `create_initial_post_types()`) and
 * any custom post type that declares `editor` support at registration.
 *
 * @param array<string, mixed> $args      Arguments passed to `register_post_type()`.
 * @param string               $post_type Post type key.
 * @return array<string, mixed> Filtered arguments.
 */
function tenoutoften_no_notes_filter_register_args( $args, $post_type ) {
	if (
		isset( $args['supports'] ) && is_array( $args['supports'] ) && isset( $args['supports']['editor'] )
		&& tenoutoften_no_notes_disabled_for( (string) $post_type )
	) {
		$args['supports']['editor'] = tenoutoften_no_notes_strip( $args['supports']['editor'] );
	}

	return $args;
}

/**
 * Whether the current request is one where Notes support is actually read.
 *
 * Core only consults `supports['editor']['notes']` in the admin, in REST requests
 * (the editor and the `/wp/v2/comments` notes endpoints) and under WP-CLI. There
 * is nothing to do on a front-end page view or a cron run, so the sweep bails
 * early there.
 *
 * @return bool
 */
function tenoutoften_no_notes_is_relevant_request() {
	return is_admin()
		|| wp_is_serving_rest_request()
		|| ( defined( 'WP_CLI' ) && WP_CLI );
}

/**
 * Sweep every registered post type late on `init`.
 *
 * Catches post types that gain Notes support through an `add_post_type_support()`
 * call made by another plugin or theme after registration.
 *
 * @return void
 */
function tenoutoften_no_notes_sweep_post_types() {
	if ( ! tenoutoften_no_notes_is_relevant_request() ) {
		return;
	}

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
		if ( ! isset( $features['editor'] ) || ! tenoutoften_no_notes_disabled_for( (string) $type ) ) {
			continue;
		}

		$stripped = tenoutoften_no_notes_strip( $features['editor'] );

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
function tenoutoften_no_notes_register_hooks() {
	add_filter( 'register_post_type_args', 'tenoutoften_no_notes_filter_register_args', 99, 2 );
	add_action( 'init', 'tenoutoften_no_notes_sweep_post_types', PHP_INT_MAX );
}

// @codeCoverageIgnoreStart
tenoutoften_no_notes_register_hooks();
// @codeCoverageIgnoreEnd
