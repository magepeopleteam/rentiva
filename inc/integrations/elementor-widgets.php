<?php
/**
 * The 9 Rentiva homepage-section widget classes. Deliberately kept in a
 * separate file from inc/integrations/elementor.php and required only from
 * inside rentiva_register_elementor_widgets() (hooked to
 * `elementor/widgets/register`): a `class X extends \Elementor\Widget_Base`
 * has its `extends` clause resolved by PHP the moment this file is *parsed*,
 * not merely executed — so this file must never be parsed at all unless
 * Elementor's classes are already loaded. A `require` guarded by an `if` (or
 * even nested inside an `if` block) in the SAME file as the class doesn't
 * achieve that: PHP compiles a whole file, including every branch, as one
 * unit before running any of it. Only a `require` sitting inside a function
 * body genuinely defers parsing of the required file until that function is
 * actually called — which is what rentiva_register_elementor_widgets() does.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class for every Rentiva homepage-section widget: no attributes, no
 * settings controls — the widget's only job is to render the same
 * template-part every classic PHP template uses, so there is exactly one
 * source of truth for each section's markup.
 */
abstract class Rentiva_Elementor_Section_Widget extends \Elementor\Widget_Base {

	/**
	 * @return string template-parts/home/{slug}.php
	 */
	abstract protected function get_section_slug();

	/**
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'rentiva' );
	}

	/**
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'rentiva', 'rental', $this->get_section_slug() );
	}

	/**
	 * The registered wp_enqueue_style() handle (inc/enqueue.php) carrying
	 * this section's CSS. Defaults to `rentiva-{slug}`; override when a
	 * section's styles live bundled inside another handle instead (e.g.
	 * Trust Strip, which lives in home.css).
	 *
	 * @return string
	 */
	protected function get_style_handle() {
		return 'rentiva-' . $this->get_section_slug();
	}

	/**
	 * Elementor calls this to know which registered styles a widget needs,
	 * and enqueues them wherever the widget actually renders — the editor
	 * canvas, an AJAX widget refresh, or a page other than the front page.
	 * inc/enqueue.php only auto-loads every section's CSS together (as
	 * `rentiva-home`'s dependencies) when `is_front_page()` is true; without
	 * this, a request that doesn't satisfy that check (Elementor's editor
	 * preview, or reusing this widget on a non-front page — both explicitly
	 * supported, see docs/elementor.md) would render the section's raw HTML
	 * with none of its CSS, e.g. every image losing its object-fit/aspect-
	 * ratio sizing and collapsing to a fraction of its intended height.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( $this->get_style_handle() );
	}

	/**
	 * No controls — content is fixed to match the mockup design exactly;
	 * copy is editable via Rentiva → Theme Settings, not per-widget.
	 *
	 * @return void
	 */
	protected function register_controls() {}

	/**
	 * @return void
	 */
	protected function render() {
		rentiva_template_part( 'template-parts/home/' . $this->get_section_slug() );
	}
}

class Rentiva_Elementor_Widget_Hero extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-hero'; }
	public function get_title() { return __( 'Rentiva: Hero', 'rentiva' ); }
	public function get_icon() { return 'eicon-slider-full-screen'; }
	protected function get_section_slug() { return 'hero'; }
}

class Rentiva_Elementor_Widget_Trust_Strip extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-trust-strip'; }
	public function get_title() { return __( 'Rentiva: Trust Strip', 'rentiva' ); }
	public function get_icon() { return 'eicon-counter'; }
	protected function get_section_slug() { return 'trust-strip'; }
	// No dedicated trust-strip.css — its styles live bundled in home.css.
	protected function get_style_handle() { return 'rentiva-home'; }
}

class Rentiva_Elementor_Widget_Categories extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-categories'; }
	public function get_title() { return __( 'Rentiva: Categories', 'rentiva' ); }
	public function get_icon() { return 'eicon-gallery-grid'; }
	protected function get_section_slug() { return 'categories'; }
}

class Rentiva_Elementor_Widget_Popular_Rentals extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-popular-rentals'; }
	public function get_title() { return __( 'Rentiva: Popular Rentals', 'rentiva' ); }
	public function get_icon() { return 'eicon-product-related'; }
	protected function get_section_slug() { return 'popular-rentals'; }
}

class Rentiva_Elementor_Widget_Promo_Banner extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-promo-banner'; }
	public function get_title() { return __( 'Rentiva: Promo Banner', 'rentiva' ); }
	public function get_icon() { return 'eicon-banner'; }
	protected function get_section_slug() { return 'promo-banner'; }
}

class Rentiva_Elementor_Widget_How_It_Works extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-how-it-works'; }
	public function get_title() { return __( 'Rentiva: How It Works', 'rentiva' ); }
	public function get_icon() { return 'eicon-number-field'; }
	protected function get_section_slug() { return 'how-it-works'; }
}

class Rentiva_Elementor_Widget_Why_Rentiva extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-why-rentiva'; }
	public function get_title() { return __( 'Rentiva: Why Rentiva', 'rentiva' ); }
	public function get_icon() { return 'eicon-check-circle-o'; }
	protected function get_section_slug() { return 'why-rentiva'; }
}

class Rentiva_Elementor_Widget_Testimonial extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-testimonial'; }
	public function get_title() { return __( 'Rentiva: Testimonial', 'rentiva' ); }
	public function get_icon() { return 'eicon-testimonial'; }
	protected function get_section_slug() { return 'testimonial'; }
}

class Rentiva_Elementor_Widget_Final_Cta extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-final-cta'; }
	public function get_title() { return __( 'Rentiva: Final CTA', 'rentiva' ); }
	public function get_icon() { return 'eicon-call-to-action'; }
	protected function get_section_slug() { return 'final-cta'; }
}
