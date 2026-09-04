# Hooks reference

All theme-defined hooks use the `rentiva_` prefix.

## Actions

| Hook | Fired in | Purpose |
|---|---|---|
| `rentiva_before_header` | `header.php`, before the site header | Insert markup before the fixed header (e.g. an announcement bar). |
| `rentiva_after_header` | `header.php`, after the site header | Insert markup right after the header. |
| `rentiva_before_footer` | `footer.php`, before the site footer | Insert markup before the footer. |
| `rentiva_after_footer` | `footer.php`, after the site footer | Insert markup after the footer, before `wp_footer()`. |

## Filters

| Hook | Default | Purpose |
|---|---|---|
| `rentiva_home_sections` | `[hero, trust-strip, categories, popular-rentals, promo-banner, how-it-works, why-rentiva, testimonial, final-cta]` | Add, remove, or reorder homepage sections without editing `front-page.php`. |

## Example: adding a homepage section

```php
add_filter( 'rentiva_home_sections', function ( $sections ) {
	// Insert a custom section right after "categories".
	$index = array_search( 'categories', $sections, true );
	array_splice( $sections, $index + 1, 0, 'my-custom-section' );
	return $sections;
} );
```

Then add `template-parts/home/my-custom-section.php` in a child theme.

## Rentiva_Rental_Adapter

Not a WordPress hook system, but the equivalent extension surface for
booking-plugin data — see `inc/integrations/booking-plugin.php` and
docs/booking-integration.md for its full method list. Call its static
methods instead of reading `rbfw_*` postmeta directly.
