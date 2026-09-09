# Theme Settings

**Rentiva → Theme Settings** (`inc/admin/theme-settings.php`) stores
sitewide, non-homepage-content settings in a single `rentiva_settings`
option (one wp_options row — deactivating the theme never leaves orphaned
rows behind). Fields are grouped into sidebar tabs; each tab below maps 1:1
to a `data-tab="{slug}"` section in `rentiva_render_settings_page()`.

| Section | Fields |
|---|---|
| Footer & Social | tagline, Twitter/X, Instagram, Facebook URLs |
| Colors | primary, primary hover, background |
| Integrations | single-item layout (Rentiva design vs. plugin's own), "List Your Item" link, default pickup hours |

**Homepage content (copy, photos, stats, features, steps, and which
categories/rental items appear) is edited directly on the Homepage page in
Elementor, not here** — see docs/elementor.md. Hero/Trust Strip/Promo
Banner/Why Rentiva/Testimonial used to have their own Theme Settings tabs;
those were removed once every homepage section widget got full Content-tab
fields (`inc/integrations/elementor-widgets.php`), so the same content was
never editable in two different places that could drift out of sync.

The underlying `hero_*` / `promo_*` / `testimonial_*` / `trust_stats` /
`why_image_id` keys in the `rentiva_settings` option **still exist and are
still read** — `rentiva_get_setting()` still works exactly as before — they
just have no admin UI of their own anymore. They now serve one purpose:
seeding the *live, pre-filled* `default` value for the matching Elementor
widget field (see "Every field pre-fills with what's actually live" in
docs/elementor.md), so a site that already had Theme Settings customization
before this change keeps showing it as each widget's starting point.

Read a setting anywhere in the theme with:

```php
rentiva_get_setting( 'hero_title', __( 'Rent. Ride. Explore.', 'rentiva' ) );
```

## Single item layout

**Rentiva design** (default) renders `templates/single/single-rbfw.php` —
the theme's own layout with the plugin's real booking form embedded inside
it (docs/booking-integration.md). **Plugin's own bundled design** bypasses
the theme's layout entirely and includes the plugin's stock template
verbatim — useful if a site owner wants zero visual customization and
maximum compatibility with a plugin update.
