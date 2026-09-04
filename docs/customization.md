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

Categories → edit a `rbfw_item_caregory` term → **Category image**. This is
a genuine theme-added field (`rentiva_category_image_id` term meta) since
the plugin's own taxonomy has no image of its own.

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
