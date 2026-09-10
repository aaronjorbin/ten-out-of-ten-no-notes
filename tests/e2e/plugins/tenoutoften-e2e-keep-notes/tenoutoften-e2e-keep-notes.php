<?php
/**
 * Plugin Name: No Notes E2E Keep Notes
 * Description: Opts the `post` post type back in via the plugin's filter, so the
 *              positive-control E2E test can confirm the Notes UI is detectable.
 *
 * @package TenOutOfTen_No_Notes
 */

add_filter(
	'tenoutoften_no_notes_disabled_for_post_type',
	static function ( bool $disabled, string $post_type ): bool {
		return 'post' === $post_type ? false : $disabled;
	},
	10,
	2
);
