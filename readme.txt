=== 10/10 - No Notes ===
Contributors: aaronjorbin
Tags: notes, block editor, collaboration, disable
Requires at least: 6.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Disables the block editor Notes feature added in WordPress 6.9.

== Description ==

WordPress 6.9 added Notes, block-level editorial comments in the post editor.
This plugin removes the "notes" editor support from every post type, so the
Notes UI no longer appears anywhere in the editor.

It uses two hooks for full coverage:

* `register_post_type_args` strips the flag as post types are registered.
* A late `init` sweep catches post types that other plugins or themes add
  Notes support to after registration.

The plugin has no settings. Activate it to turn Notes off; deactivate to turn
it back on.

== Frequently Asked Questions ==

= Does this delete my existing notes? =

No. Notes are stored as comments with the type `note`. This plugin only hides
the editor feature; it never touches comment data. Deactivating restores the
feature and any existing notes.

= Can I keep Notes on for one post type? =

Not in this version. The plugin is a hard off switch for all post types.

== Development ==

Local site (requires Docker + Node). `.wp-env.json` runs the latest WordPress with
this plugin active:

    npm install
    npm run env:start

    Site:  http://localhost:60987          (admin / password)
    Tests: http://localhost:60988
    WP-CLI: npm run env:cli -- <command>

Install dev dependencies and run the test suite:

    composer install
    composer test

Line coverage (requires Xdebug, PCOV, or phpdbg):

    composer coverage

The dev tooling (PHPUnit 9, PHPCS, PHPStan) needs PHP 7.4 or newer to run.

Coding standards (full WordPress ruleset + PHP cross-version compatibility) and
static analysis (PHPStan, level max):

    composer lint
    composer analyze
    composer check   # lint + analyze + test

The suite runs against a small faithful set of WordPress shims (`tests/wp-stubs.php`),
whose post-type-support functions are copied verbatim from `wp-includes/post.php`,
so no WordPress install is needed. Behaviour was also verified end to end against a
real WordPress build.

== Changelog ==

= 1.0.0 =
* Initial release.
