# Customization

## "Key Specifications" and "What's Included" data convention

The single-item page's spec grid and included-items checklist both read the
plugin's own **Feature List** field (`rbfw_feature_category`, edited per
item in the plugin's Modern Editor — not a theme-added field). Rentiva
interprets it like this:

- The **first** feature category becomes the "Key Specifications" grid.
  Enter each row as `Label: Value` (e.g. `Frame: Aluminum`, `Weight: 12.5 kg`).
  A row without a colon is shown as a label with no value.
- The **second** feature category (if you add one) becomes the "What's
  Included" checklist. Enter each row as a plain item (e.g. `Helmet`,
  `Bike Lock`) — no colon needed here.

This is why the same admin field powers two different-looking sections: it
avoids inventing a duplicate meta field for data the plugin already stores.

## Category tile images

Rental Items → Categories → click **Edit** on a category's card → **Category
image**. Stored as `rentiva_category_image_id` term meta — that name is
historical (it was theme-added when this screen was WordPress's native
edit-tags.php list); the field itself is now entirely owned and rendered by
the booking plugin's Categories screen
(`admin/RBFW_Category_Manager.php` in
booking-and-rental-manager-for-woocommerce, including its own media-picker
JS), so it works with the Rentiva theme deactivated too. The meta key was
kept as-is rather than renamed purely to avoid touching every front-end
template that already reads it by this name (template-parts/home/categories.php
and friends, via `rentiva_get_homepage_categories()` in
inc/template-functions.php) for no functional benefit.

The whole screen — header stats, search/filter/sort toolbar, grid/list card
view, and the single Add/Edit modal (AJAX, no page reload) — replaces
WordPress's native edit-tags.php list and still just calls
wp_insert_term()/wp_update_term()/wp_delete_term() under the hood, so
nothing about how terms themselves are stored changed. It fires
`rbfw_category_manager_modal_fields`/`_term_saved` action hooks purely as an
optional extension point for a theme/plugin that wants to add its *own*
extra field — the category image is no longer one of them.

## Colors, fonts, spacing

The palette and type scale are locked in `theme.json` and mirrored as CSS
custom properties in `assets/css/variables.css`. To change the accent color
site-wide, use Rentiva → Theme Settings → Colors rather than editing CSS —
it updates both the block-editor palette and the front end consistently.

## Overriding a single template-part in a child theme

Every section/card/item partial lives under `template-parts/`. A child
theme can override any of them by creating the same relative path — e.g.
`template-parts/cards/rental-card.php` — WordPress's template-part
resolution finds the child theme's copy first automatically.

**Exception:** `templates/single/single-rbfw.php` is resolved by the
*plugin's* own lookup, not WordPress's — see docs/child-theme.md for why
overriding it from a child theme needs a full-file copy, not a partial one.
