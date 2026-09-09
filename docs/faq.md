# FAQ

**Does Rentiva work without Booking and Rental Manager for WooCommerce?**
Rentiva renders a normal WordPress site (pages, posts, search, 404) without
it, but the homepage's Popular Rentals section falls back to a small demo
dataset, category tiles fall back to a static category list, and the
archive/single-item templates that depend on `rbfw_item` won't have
anything to show. The plugin is a required dependency for the theme's
actual rental features.

**Can I use a different booking/rental plugin instead?**
Not without rewriting `inc/integrations/booking-plugin.php` — it's built
specifically around this plugin's meta keys, taxonomies, and template
mechanism (docs/booking-integration.md). Every template calls the adapter,
never the plugin directly, so a rewrite is contained to that one file plus
`templates/single/single-rbfw.php`.

**Do I have to use Elementor?**
Elementor is required (see `style.css`), but only its 9 Rentiva widgets are
provided for building *additional* pages — the real homepage, archive, and
single-item pages are plain PHP templates and don't require touching
Elementor at all.

**Where do reviews come from?**
Ordinary WordPress comments on the rental item itself, with a star-rating
field the theme adds. See docs/booking-integration.md.

**Can multiple vendors list their own rentals?**
No — this plugin is a single-catalog-owner rental engine, not a multi-vendor
marketplace. "List Your Item" in the header/CTA is a marketing link you
point at your own contact/onboarding process (Rentiva → Theme Settings),
not a frontend submission form.

**How do I change the accent color?**
Rentiva → Theme Settings → Colors — not by editing CSS. See
docs/customization.md.
