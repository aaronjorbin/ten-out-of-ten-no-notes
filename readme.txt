=== 10/10 - No Notes ===
Contributors: aaronjorbin
Tags: notes, block editor, collaboration, disable
Requires at least: 6.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Disables the ability to add Notes, the block editor feature added in WordPress 6.9.

== Description ==

WordPress 6.9 added Notes, block-level editorial comments in the post editor.
This plugin removes the "notes" editor support from every post type, so the
Notes UI no longer appears in the editor and the REST API stops accepting new
notes.

It uses two hooks:

* `register_post_type_args` strips the flag as each post type is registered.
* A late `init` sweep catches post types that another plugin or theme adds
  Notes support to *after* registration.

Out of the box the plugin has no settings — activate it to turn Notes off,
deactivate to turn them back on.

= Keeping Notes for some post types =

Perfect scores are overrated. If you want Notes on for a post type or two —
call it a solid 9/10, some notes — use the `tenoutoften_no_notes_disabled_for_post_type`
filter and return `false` for those types:

    add_filter(
        'tenoutoften_no_notes_disabled_for_post_type',
        function ( $disabled, $post_type ) {
            // Keep Notes for the "briefing" post type, disable everywhere else.
            return 'briefing' === $post_type ? false : $disabled;
        },
        10,
        2
    );

== Frequently Asked Questions ==

= Does this delete my existing notes? =

No. Notes are stored as comments with the type `note`. This plugin only removes
the editor support; it never touches comment data. Deactivating restores the
feature and every existing note.

= Can I keep Notes on for one post type? =

Yes — use the `tenoutoften_no_notes_disabled_for_post_type` filter (see above).

= A post type still has Notes even though the plugin is active =

The `init` sweep runs once, at priority `PHP_INT_MAX`. If another plugin or theme
calls `add_post_type_support( $type, 'editor', array( 'notes' => true ) )` *after*
that — for example on `wp_loaded`, `admin_init`, or `rest_api_init` — the sweep
has already been and gone, so Notes come back for that type. Post types that are
*registered* late are still handled, because the `register_post_type_args` filter
runs inside every `register_post_type()` call. If you hit this, disable the other
extension's late call or re-run `tenoutoften_no_notes_sweep_post_types()` after it.

== Development ==

Local site (requires Docker + Node). `.wp-env.json` runs the latest WordPress with
this plugin active:

    npm install
    npm run env:start

    Site:  http://localhost:60987          (admin / password)
    Tests: http://localhost:60988
    WP-CLI: npm run env:cli -- <command>

Unit tests (WordPress shims, no install needed) plus coding standards and static
analysis. The dev tooling (PHPUnit 9, PHPCS, PHPStan) needs PHP 7.4 or newer:

    composer install
    composer check          # lint + analyze + test
    composer coverage       # line coverage (needs Xdebug, PCOV, or phpdbg)

Integration and browser end-to-end tests run inside wp-env against real
WordPress (the E2E suite uses @wordpress/e2e-test-utils-playwright and needs
`npx playwright install chromium` once):

    npm run env:start
    npm run test:integration
    npm run test:e2e

GitHub Actions runs the unit suite (PHP 7.4–8.3), coding standards, static
analysis, and the integration + E2E suites against both the latest stable
WordPress and trunk on every push and pull request.

== Changelog ==

= 1.1.0 =
* Add the `tenoutoften_no_notes_disabled_for_post_type` filter to keep Notes for
  specific post types.
* Skip the `init` sweep on front-end and cron requests, where Notes support is
  never read.
* Add WordPress integration and Playwright E2E test suites, plus GitHub Actions
  running everything against the latest stable WordPress and trunk.
* Reworded the description: the plugin disables *adding* Notes; existing note
  comments are untouched.

= 1.0.0 =
* Initial release.
