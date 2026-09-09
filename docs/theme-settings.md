# Theme Settings

**Rentiva → Theme Settings** (`inc/admin/theme-settings.php`) stores every
override in a single `rentiva_settings` option (one wp_options row —
deactivating the theme never leaves orphaned rows behind). Fields are
grouped into sidebar tabs on the settings screen; each tab below maps 1:1 to
a `data-tab="{slug}"` section in `rentiva_render_settings_page()`.

| Section | Fields |
|---|---|
| Hero | eyebrow, headline, subheading, background photo |
| Trust Strip | 4 stat value/label pairs |
| Promo Banner | badge text, headline, text, background photo |
| Why Rentiva | photo |
| Testimonial | quote, name, role, avatar |
| Footer & Social | tagline, Twitter/X, Instagram, Facebook URLs |
| Colors | primary, primary hover, background |
| Integrations | single-item layout (Rentiva design vs. plugin's own), "List Your Item" link, default pickup hours |

Any field left blank falls back to the built-in default matching the
approved mockup design — saving the settings screen once never blanks out
the homepage.

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
