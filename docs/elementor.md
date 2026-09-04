# Elementor widgets

Elementor is a required plugin (see `style.css`'s `Requires Plugins`
header). Rentiva registers a **"Rentiva"** widget category
(`inc/integrations/elementor.php`) with 9 widgets — one per homepage
section:

- Rentiva: Hero
- Rentiva: Trust Strip
- Rentiva: Categories
- Rentiva: Popular Rentals
- Rentiva: Promo Banner
- Rentiva: How It Works
- Rentiva: Why Rentiva
- Rentiva: Testimonial
- Rentiva: Final CTA

Each widget has **no settings controls** by design: it renders the exact
same `template-parts/home/{slug}.php` file the real homepage uses (via
`Rentiva_Elementor_Section_Widget::render()`), so a page built with these
widgets is pixel-identical to the built-in homepage. Content is edited the
same way the homepage's is — through Rentiva → Theme Settings
(docs/theme-settings.md), not per-widget fields.

## Why the real homepage isn't built with Elementor

`front-page.php` renders these sections directly via PHP for guaranteed
performance and fidelity to the approved design. The widgets exist so
admins can reuse the same sections on *other* pages (a secondary landing
page, a campaign page, etc.) without hand-coding them again.

## Extending

To add a 10th section widget, create `template-parts/home/{slug}.php`,
then add a concrete class in `inc/integrations/elementor.php`:

```php
class Rentiva_Elementor_Widget_My_Section extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-my-section'; }
	public function get_title() { return __( 'Rentiva: My Section', 'rentiva' ); }
	public function get_icon() { return 'eicon-code'; }
	protected function get_section_slug() { return 'my-section'; }
}
```

...and register it in `rentiva_register_elementor_widgets()`.
