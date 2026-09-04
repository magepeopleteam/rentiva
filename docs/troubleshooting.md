# Troubleshooting

**The homepage shows placeholder gradients instead of photos.**
No image has been set for that section yet. Set the Hero/Promo/Why Rentiva
photos under Appearance → Rentiva Settings, or add a featured image to the
relevant rental items for card photos.

**Rental cards show demo items I didn't create.**
Those are the theme's bundled fallback dataset, shown only when the booking
plugin has zero published `rbfw_item` posts (so the homepage never renders
empty). Publish real rental items and the fallback disappears automatically
— see `rentiva_get_rental_cards()` in `inc/template-functions.php`.

**The single-item page looks like the plugin's own default design, not
Rentiva's.**
Check Appearance → Rentiva Settings → Integrations → "Single item layout" —
it may be set to "Plugin's own bundled design".

**A rental item's "Key Specifications" section is empty.**
That section reads the plugin's Feature List field on the item — see
docs/customization.md for the exact `Label: Value` convention it expects.

**Category tiles on the homepage show a generic gradient.**
Add a Category image under Categories → edit that `rbfw_item_caregory` term
— see docs/customization.md.

**The booking form's Reserve/Book Now button stays disabled.**
This is the plugin's own validation (e.g. dates not yet selected, required
fields incomplete) — Rentiva only re-skins this form with CSS and never
changes its logic. Check the item's configuration in the plugin's own admin
screens.

**Elementor widgets aren't showing up.**
Confirm Elementor is active — the widgets only register when
`did_action( 'elementor/loaded' )` is true (`inc/integrations/elementor.php`).

**I get a PHP fatal error mentioning `RBFW_` or `Rentiva_Rental_Adapter`.**
Confirm Booking and Rental Manager for WooCommerce is installed AND active —
`Rentiva_Rental_Adapter::is_active()` guards most call sites, but a template
calling an adapter method directly outside that guard will fail if the
plugin isn't present.
