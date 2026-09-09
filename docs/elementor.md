# Elementor widgets

**Elementor's own `frontend.css` ships `.elementor img { height: auto }`.**
That's a compound selector (specificity 0,1,1), which beats any of this
theme's single-class "fill the container" image rules like
`.rentiva-category-card__image { height: 100% }` (specificity 0,1,0) —
confirmed via Chrome DevTools' `CSS.getMatchedStylesForNode`. Wherever a
cover-fit image sits inside `.elementor …` markup (i.e. every homepage
section, since they're all Elementor widgets now), the image's rendered
height silently falls back to its intrinsic aspect ratio instead of
filling its box. Whether this is visible depends on the specific photo:
if the resulting auto-height happens to exceed the container, an
`overflow: hidden` ancestor clips the excess and it looks fine by pure
coincidence (this is what made Popular Rentals look correct while
Categories, with wider/shorter photos, visibly left a gap at the bottom).
Every affected rule — `.rentiva-hero__image`, `.rentiva-category-card__image`,
`.rentiva-promo-banner__image`, `.rentiva-why-rentiva__image`,
`.rentiva-rental-card__image` (`components.css`), and
`.rentiva-testimonial__avatar img` — now sets `height: 100% !important` for
exactly this reason. Any new cover-fit image style added to a homepage
section widget must do the same, or it will intermittently break depending
on the aspect ratio of whatever photo happens to be uploaded.

Elementor is a required plugin (see `style.css`'s `Requires Plugins`
header). Rentiva registers a **"Rentiva"** widget category
(`inc/integrations/elementor.php`) with 9 widgets — one per homepage
section, with the widget classes themselves living in
`inc/integrations/elementor-widgets.php` (see "Extending" below for why
that's a separate file):

- Rentiva: Hero
- Rentiva: Trust Strip
- Rentiva: Categories
- Rentiva: Popular Rentals
- Rentiva: Promo Banner
- Rentiva: How It Works
- Rentiva: Why Rentiva
- Rentiva: Testimonial
- Rentiva: Final CTA

Each widget has **no Content-tab controls** by design: it renders the exact
same `template-parts/home/{slug}.php` file the real homepage uses (via
`Rentiva_Elementor_Section_Widget::render()`), so a page built with these
widgets is pixel-identical to the built-in homepage. Copy/images are edited
the same way the homepage's is — through Rentiva → Theme Settings
(docs/theme-settings.md), not per-widget fields.

**Every widget DOES have Style-tab controls** — a Text Color + Typography
(font family/size/weight/line-height/letter-spacing) pair per meaningful
text element in that section (e.g. Hero gets Eyebrow/Headline/Headline
Accent Word/Subheading; Trust Strip gets Stat Value/Stat Label). These are
registered via `Rentiva_Elementor_Section_Widget::add_text_style_section()`
(`inc/integrations/elementor-widgets.php`), which wires a `Controls_Manager::COLOR`
control and a `Group_Control_Typography` group to a CSS selector through
Elementor's own `selectors` mechanism (`'{{WRAPPER}} .some-class' => 'color:
{{VALUE}};'`) — Elementor generates and scopes the resulting CSS itself
(via `{{WRAPPER}}`, unique per widget instance), so nothing in `render()` or
the template-parts needs to change, and it's safe even for selectors built
on classes shared across multiple sections (`.rentiva-h2`, `.rentiva-lead`,
etc.) since the override never leaks outside that one widget instance. To
add style controls for a 10th widget (or a new element on an existing one),
call `add_text_style_section( $key, $label, $selector )` from that widget's
`register_controls()` with the element's real CSS class from its
template-part — `$key` becomes the control-id prefix (`{$key}_color`,
`{$key}_typography_typography`, etc.), so keep it unique within that widget.

**These controls only appear inside wp-admin.** Elementor 4.x's
`\Elementor\Core\Frontend\Performance::should_optimize_controls()` segregates
"style" controls (anything with `selectors`, e.g. Color/Typography) into a
separate internal bucket whenever `is_admin()` is false — `get_controls()`
then simply won't return them. This is intentional front-end-performance
behavior, not a bug: the real Elementor editor always runs inside
`wp-admin`, so it always sees the full set. Don't be alarmed if a CLI/WP-CLI
script that boots WordPress outside `wp-admin` (e.g. via `wp-load.php`
directly) shows these controls missing from `get_controls()` — verify by
forcing admin context instead (`define( 'WP_ADMIN', true )` before loading
WordPress), not by trusting a bare front-end-context script.

**Every widget declares its own CSS via `get_style_depends()`.**
`inc/enqueue.php` only auto-loads every section's CSS together (as
`rentiva-home`'s dependencies) when `is_front_page() && !is_paged()` is
true — which isn't guaranteed in every context these widgets can render in
(Elementor's editor preview, an AJAX widget refresh, or reusing a widget on
a page other than the front page, which is an explicitly supported use
case below). Without a widget-level dependency, any of those would render
the section's raw HTML with none of its CSS — e.g. every image losing its
`object-fit`/`aspect-ratio` sizing and collapsing to a fraction of its
intended height instead of filling its card. `get_style_depends()`
(`inc/integrations/elementor-widgets.php`) is Elementor's own supported
mechanism for this (the same one its built-in widgets use) — it returns
the `rentiva-{slug}` handle already registered in `inc/enqueue.php`, or
`rentiva-home` for Trust Strip specifically, since that section has no
dedicated stylesheet of its own (its rules live bundled in `home.css`).
Any future section widget must do the same — never assume `rentiva-home`'s
`is_front_page()` gate will have already loaded its CSS.

## The real homepage is built with these widgets

On theme activation, `rentiva_flag_activation()` (`inc/admin/setup-wizard.php`)
calls `rentiva_setup_homepage_page()` (`inc/helpers.php`), which creates a
"Homepage" page pre-built out of exactly these 9 widgets — one per section,
in the same order as the fallback layout — and sets it as the static front
page. That's what makes the homepage fully editable in Elementor (rearrange,
remove, or duplicate sections) right from the first run.

`front-page.php` only falls back to rendering the sections directly via PHP
(`rentiva_homepage_uses_custom_builder()` returns false) when that page is
missing or empty — e.g. Elementor wasn't active yet at activation time. The
same widgets are also handy for reusing a section on *other* pages (a
secondary landing page, a campaign page, etc.) without hand-coding them
again.

**Seeded section settings matter — don't drop them.** Every section
`rentiva_seed_homepage_elementor_data()` writes gets explicit
`'layout' => 'full_width'`, `'gap' => 'no'`, and zeroed margin/padding.
Each widget already renders its own full-bleed `<section>` with its own
CSS-controlled max-width/padding (matching `mockup/rentiva.html` exactly),
so Elementor's own defaults — a 1140px "Boxed" content width and a 10px
column-gap padding around every widget — would otherwise visibly narrow
and inset every section the moment the page is opened, and an admin would
have to know to fix each section's Content Width/Gap by hand to match the
design. Seeding these up front means the customer never has to touch
per-section settings for the design to look right.

## Fixing an existing "Homepage" page that's out of sync

`rentiva_setup_homepage_page()` only calls
`rentiva_seed_homepage_elementor_data()` while `_elementor_data` is still
empty — it never overwrites a page an admin has actually edited/saved. If
the seeding logic itself changes (e.g. a future fix to the settings above)
and an already-seeded "Homepage" page needs to pick it up, re-run seeding
directly for that page id rather than through the normal setup flow:

```php
rentiva_seed_homepage_elementor_data( rentiva_get_homepage_page_id() );
```

Only do this when you're sure no real admin edits exist yet to lose —
it unconditionally overwrites `_elementor_data`.

## Extending

To add a 10th section widget, create `template-parts/home/{slug}.php`, then
add a concrete class to `inc/integrations/elementor-widgets.php` (**not**
`elementor.php`):

```php
class Rentiva_Elementor_Widget_My_Section extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-my-section'; }
	public function get_title() { return __( 'Rentiva: My Section', 'rentiva' ); }
	public function get_icon() { return 'eicon-code'; }
	protected function get_section_slug() { return 'my-section'; }
}
```

...and register it in `rentiva_register_elementor_widgets()`
(`inc/integrations/elementor.php`).

**Why the widget classes live in their own file:** every widget class here
`extends \Elementor\Widget_Base`, and PHP resolves an `extends` clause the
moment the file containing it is *parsed* — not merely executed. A class
declaration guarded by `if ( rentiva_has_elementor() ) { ... }` in the
*same* file as an unconditional `require_once` doesn't help, because PHP
compiles a whole file (every branch) as one unit before running any of it.
`elementor-widgets.php` is only ever `require_once`'d from inside
`rentiva_register_elementor_widgets()`'s function body, which itself only
ever runs as a callback for Elementor's own `elementor/widgets/register`
hook — the one point at which `\Elementor\Widget_Base` is guaranteed to
already exist. A `require` inside a function body genuinely defers parsing
of that file until the function is called, unlike a class nested in an
`if` block within the same already-being-compiled file. Keep any future
section widget classes in that file, never in `elementor.php` itself.
