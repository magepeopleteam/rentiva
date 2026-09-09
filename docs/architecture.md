# Architecture

Rentiva is a classic (non-FSE) PHP theme with one rule at its center:
**Rentiva owns presentation; the booking plugin owns rentable items,
pricing, and availability; WooCommerce owns orders and payments.** A
template that computes a price or an availability decision is a bug — see
docs/booking-integration.md for the full ownership split.

## Routing

| Request | Template |
|---|---|
| `/` | `front-page.php` (always wins) |
| Single `rbfw_item` | `templates/single/single-rbfw.php` (picked up automatically by the plugin's own template-resolution — see docs/booking-integration.md) |
| `rbfw_item` archive | `archive-rbfw_item.php` |
| `rbfw_item_caregory` term | `taxonomy-rbfw_item_caregory.php` |
| `rbfw_item_location` term | `taxonomy-rbfw_item_location.php` |
| Everything else | `page.php` / `single.php` (via `index.php` fallback) / `search.php` / `404.php` |

## Bootstrap order (`functions.php`)

```
helpers → setup → enqueue → template-functions → template-hooks
  → integrations/booking-plugin → integrations/woocommerce → integrations/elementor
  → demo-import/sample-data → demo-import/importer → admin/admin
```

Order matters: helpers before anything that calls them, setup before
enqueue, integrations after the shared render layer (`template-functions.php`)
so they can reuse it, admin last since it's admin-only.

## The shared render layer

`inc/template-functions.php` is the single funnel every classic PHP
template, Elementor widget, and AJAX handler renders through —
`rentiva_rental_grid()`, `rentiva_the_star_rating()`, `rentiva_primary_nav()`,
etc. Nothing duplicates markup by hand-copying a template-part's HTML
elsewhere; everything calls the same function or `get_template_part()`.

## Homepage sections

Elementor is a hard theme dependency (`style.css`'s `Requires Plugins`), and
the homepage is meant to be a real, fully editable Elementor page: on theme
activation, `rentiva_flag_activation()` (`inc/admin/setup-wizard.php`) calls
`rentiva_setup_homepage_page()` (`inc/helpers.php`), which creates a
"Homepage" page pre-built out of the theme's 9 Elementor section widgets —
hero → trust-strip → categories → popular-rentals → promo-banner →
how-it-works → why-rentiva → testimonial → final-cta (docs/elementor.md) —
and sets it as the static front page. `front-page.php` then renders that
page's own content via `the_content()`.

`front-page.php` only falls back to rendering
`template-parts/home/{slug}.php` directly (the same `rentiva_home_sections`
filterable list, hardcoded in PHP) when no such page exists yet — e.g.
Elementor wasn't active at activation time, or an admin reset Settings →
Reading back to "Your latest posts". `rentiva_homepage_uses_custom_builder()`
in `inc/helpers.php` is the switch between the two, and **Rentiva → Setup**
(and its compact Dashboard widget) offers a "Create editable Homepage page"
button to (re)provision it by hand.

## Single-item page

`templates/single/single-rbfw.php` owns the page chrome (breadcrumb,
gallery/info/specs/about/pickup/reviews column, sticky booking sidebar,
mobile bottom bar, similar items) and pulls each section from
`template-parts/item/*.php`. The interactive booking form itself is the
plugin's own, unmodified — see docs/booking-integration.md.

## Assets

`inc/enqueue.php` registers every stylesheet/script up front, then
conditionally enqueues only what the current template needs (front page,
archive/taxonomy, single item, WooCommerce pages). Nothing loads globally
that isn't needed on every page — see the file's `rentiva_enqueue_assets()`
for the exact conditions.

## Design tokens

`theme.json` locks the color palette, two font families (Plus Jakarta Sans
for display, Inter for body), and a fixed font-size/spacing scale — matching
the approved mockup design exactly. `assets/css/variables.css` mirrors the
same tokens as CSS custom properties for the theme's own (non-block) markup.
