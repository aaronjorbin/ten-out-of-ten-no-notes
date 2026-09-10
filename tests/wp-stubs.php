<?php
/**
 * Minimal, faithful WordPress shims so the plugin's hooks can run without a full
 * WordPress bootstrap.
 *
 * The post-type-support functions are copied verbatim from wp-includes/post.php
 * so the shapes they produce match core exactly. register_post_type() mirrors the
 * relevant parts of WP_Post_Type::set_props() / ::add_supports().
 *
 * @package Tototen_No_Notes
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Intentionally shadowing core functions.
// phpcs:disable WordPress.WP.GlobalVariablesOverride -- The shims own this state.
// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter -- Signatures mirror core.
// phpcs:disable Squiz.Commenting.FunctionComment -- Thin stubs.

$GLOBALS['_wp_post_type_features'] = array();
$GLOBALS['__wp_hooks']             = array();
$GLOBALS['__wp_post_types']        = array();

/**
 * Reset all shim state. Call from a test's setUp().
 *
 * @return void
 */
function tototen_test_reset() {
	$GLOBALS['_wp_post_type_features'] = array();
	$GLOBALS['__wp_hooks']             = array();
	$GLOBALS['__wp_post_types']        = array();
}

/*
 * Hook API: priority-ordered, enough for filters and actions.
 */

function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
	$GLOBALS['__wp_hooks'][ $tag ][] = array(
		'priority' => $priority,
		'cb'       => $callback,
	);
	usort(
		$GLOBALS['__wp_hooks'][ $tag ],
		static function ( $a, $b ) {
			return $a['priority'] <=> $b['priority'];
		}
	);
	return true;
}

function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
	return add_filter( $tag, $callback, $priority, $accepted_args );
}

function apply_filters( $tag, $value, ...$rest ) {
	foreach ( $GLOBALS['__wp_hooks'][ $tag ] ?? array() as $hook ) {
		$value = $hook['cb']( $value, ...$rest );
	}
	return $value;
}

function do_action( $tag, ...$rest ) {
	foreach ( $GLOBALS['__wp_hooks'][ $tag ] ?? array() as $hook ) {
		$hook['cb']( ...$rest );
	}
}

/*
 * Post type support API: copied verbatim from wp-includes/post.php.
 */

function add_post_type_support( $post_type, $feature, ...$args ) {
	global $_wp_post_type_features;

	$features = (array) $feature;
	foreach ( $features as $feature ) {
		if ( $args ) {
			$_wp_post_type_features[ $post_type ][ $feature ] = $args;
		} else {
			$_wp_post_type_features[ $post_type ][ $feature ] = true;
		}
	}
}

function remove_post_type_support( $post_type, $feature ) {
	global $_wp_post_type_features;

	unset( $_wp_post_type_features[ $post_type ][ $feature ] );
}

function get_all_post_type_supports( $post_type ) {
	global $_wp_post_type_features;

	return $_wp_post_type_features[ $post_type ] ?? array();
}

function post_type_supports( $post_type, $feature ) {
	global $_wp_post_type_features;

	return isset( $_wp_post_type_features[ $post_type ][ $feature ] );
}

function get_post_types( $args = array(), $output = 'names', $operator = 'and' ) {
	return array_keys( $GLOBALS['__wp_post_types'] );
}

/*
 * register_post_type(): mirrors the two behaviours the plugin relies on --
 * applying the `register_post_type_args` filter, then collecting `supports`
 * into $_wp_post_type_features the way WP_Post_Type::add_supports() does.
 */

function register_post_type( $post_type, $args = array() ) {
	$args = apply_filters( 'register_post_type_args', $args, $post_type );

	$GLOBALS['__wp_post_types'][ $post_type ] = true;

	$supports = array_key_exists( 'supports', $args ) ? $args['supports'] : array( 'title', 'editor', 'autosave' );

	if ( false === $supports || empty( $supports ) ) {
		return;
	}

	foreach ( (array) $supports as $feature => $feature_args ) {
		if ( is_array( $feature_args ) ) {
			add_post_type_support( $post_type, $feature, $feature_args );
		} else {
			add_post_type_support( $post_type, $feature_args );
		}
	}
}
