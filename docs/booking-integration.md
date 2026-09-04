# Booking integration

Rentiva integrates with **Booking and Rental Manager for WooCommerce**
(`booking-and-rental-manager-for-woocommerce`, `RBFW_*` classes). This
document is the single source of truth for how — read it before touching
`inc/integrations/booking-plugin.php` or any single-item template.

## Ownership split

- **The plugin owns**: the `rbfw_item` post type, the `rbfw_item_caregory` /
  `rbfw_item_location` taxonomies, all pricing/availability meta, the real
  booking form (date pickers, AJAX price calculation, stock checks, cart
  submission), and a hidden "shadow" WooCommerce product per item used only
  to route checkout through WooCommerce.
- **The theme owns**: page layout/chrome, card design, homepage sections,
  and two small enrichment fields the plugin has no equivalent for (see
  "Theme-owned data" below).
- **`Rentiva_Rental_Adapter`** (`inc/integrations/booking-plugin.php`) is the
  *only* code allowed to call `RBFW_*` classes or read `rbfw_*` postmeta.
  Every template, card, and Elementor widget goes through it.

## The permanent taxonomy typo

The category taxonomy is registered by the plugin as **`rbfw_item_caregory`**
(not "category" — this is a real, permanent typo in the plugin, confirmed in
its `admin/taxonomy_register.php`). It must be used verbatim everywhere:
template filenames (`taxonomy-rbfw_item_caregory.php`), `get_the_terms()`
calls, `tax_query` arrays, and Elementor/query filters. Do not "fix" it.

## Template ownership — no filter override needed

RBFW's own `RBFW_Frontend::single_template()` filter always wins over
WordPress's native template hierarchy, and it resolves the actual file
through `RBFW_Function::get_template_path()`, which checks
`get_stylesheet_directory() . '/templates/...'` **before** falling back to
the plugin's own bundled `templates/` folder. That means:

- `templates/single/single-rbfw.php` in this theme is picked up
  automatically and becomes the entire single-item page — no
  `add_filter( 'single_template', ... )` needed in the theme at all.
- A theme-root `single-rbfw_item.php` would be **dead code** (RBFW's filter
  discards WordPress's own template-hierarchy result), so this theme
  deliberately does not ship one.
- `templates/single/{default,muffin}/*.php` and `templates/forms/*.php`
  stay **100% plugin-owned** — the theme never overrides them, so real
  date-pickers/AJAX price calc/stock checks/cart submission keep working
  across plugin updates.
- `archive-rbfw_item.php` in the theme root **is** picked up by WordPress's
  normal hierarchy (confirmed: no `archive_template`/`template_include`
  interception exists anywhere in the plugin).

## Embedding the real booking form

`Rentiva_Rental_Adapter::render_booking_form( $item_id )` resolves the
item's type → template file name via `RBFW_Frontend::get_rent_type_template()`
and `include`s the plugin's real `forms/{type}-registration.php` partial
directly — the same 3–4 lines the plugin's own single-item wrapper uses. All
interactivity (flatpickr date pickers, off-day blocking, AJAX price
recalculation, stock checks, nonces, cart submission) is the plugin's
original, unmodified code.

`assets/css/booking.css` re-skins that real markup to match the design —
targeting real, confirmed class names (`.rbfw-drp-wrapper`, `.rbfw-select`,
`.rbfw_bikecarmd_price_result`, `.rbfw-book-now-btn`, etc.) — and hides a
couple of elements that would otherwise duplicate what the theme's own
card/sidebar chrome already shows (`.pricing-content-container`,
`.rbfw-sd-rate-box`). Nothing is ever removed from the DOM, only
`display:none`, so no plugin JS listener is ever broken.

`assets/js/quantity-stepper.js` progressively enhances the plugin's real
`<select name="rbfw_item_quantity">` with a −/+ stepper UI: it changes the
select's value and dispatches a native `change` event, so the plugin's own
price-recalculation script still receives the event exactly as if the
visitor had used the native dropdown.

## Theme-owned data (genuine gaps, not duplicated plugin fields)

Two small enrichments exist because the plugin has no equivalent field —
each is documented so it survives plugin updates and isn't mistaken for
plugin data:

1. **Category tile image** (`rentiva_category_image_id` term meta on
   `rbfw_item_caregory`) — the plugin's taxonomy has no image field.
   Editable from Categories → edit term (see `inc/admin/category-image-metabox.php`).
2. **Reviews** — the plugin has no rating/review system. `rbfw_item`
   supports native WordPress comments, so reviews are ordinary comments with
   a theme-added `rentiva_rating` comment-meta star score
   (`inc/template-functions.php`). Reviews are **not** attached to the
   plugin's hidden shadow WooCommerce product — that product is hard-404'd
   by the plugin on any direct visit, making it a dead end for a review URL.

Everything else — "Key Specifications" and "What's Included" on the
single-item page — reuses the plugin's own **Feature List** field
(`rbfw_feature_category` postmeta, edited via the plugin's Modern Editor).
See docs/customization.md for the exact data convention.

## Shadow WooCommerce product

Every `rbfw_item` gets an auto-created hidden `product` post (price 0.01,
tagged `exclude-from-catalog`/`exclude-from-search`) purely so checkout can
go through WooCommerce. The plugin already 404s direct visits to it and
hides it from wp-admin's product list and the shop/search — Rentiva's own
`inc/integrations/woocommerce.php` intentionally adds **no** catalog-
visibility logic of its own; doing so would be redundant and could fight the
plugin's own handling.

## Price display vs. real pricing

`Rentiva_Rental_Adapter`'s pricing methods (`get_display_price()`,
`get_secondary_price()`, etc.) only ever produce **display estimates** for
cards, the hero, and the detail page's price block. The actual
checkout-time calculation — including seasonal pricing, multi-day discounts,
security deposits, and fees — remains exclusively inside the plugin's own
procedural pricing functions and its real booking form. No template in this
theme performs that math.
