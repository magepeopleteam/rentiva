# Child themes

Standard WordPress child-theme rules apply: create a `style.css` with a
`Template: rentiva` header and enqueue the parent stylesheet, then override
any `template-parts/*.php` file by recreating its relative path in the
child theme.

## The one exception: `templates/single/single-rbfw.php`

This file is **not** resolved by WordPress's normal parent/child template
lookup — it's resolved by the plugin's own
`RBFW_Function::get_template_path()`, which checks
`get_stylesheet_directory()` (the **child** theme's directory when a child
theme is active) and, if the file isn't found there, falls through directly
to the **plugin's own bundled default** — not to the parent theme's copy.

**Practical consequence:** if you activate a Rentiva child theme and want to
customize the single-item page, you must copy the *entire*
`templates/single/single-rbfw.php` file into the child theme at the same
path (`{child-theme}/templates/single/single-rbfw.php`) — a partial
override, or relying on the parent theme's copy, will not work. The same
applies to anything under `templates/` more generally, since that whole
directory is the plugin's override namespace, not the standard WordPress
one. See docs/booking-integration.md for why this mechanism exists.

Every other directory (`template-parts/`, `assets/`, `inc/`) follows normal
WordPress parent/child resolution.
