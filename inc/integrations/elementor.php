<?php
/**
 * Elementor integration — one widget per homepage section, so admins can
 * reuse the exact same sections (hero, categories, popular rentals, etc.)
 * to compose other pages. Elementor is a hard theme dependency (see
 * style.css's "Requires Plugins" header).
 *
 * The real homepage (front-page.php) does NOT use these widgets — it
 * renders template-parts/home/*.php directly for guaranteed performance
 * and pixel fidelity. Each widget below shares that exact same render
 * function, so a page built with them looks identical to the built-in
 * homepage.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! rentiva_has_elementor() ) {
	return;
}

/**
 * Register the "Rentiva" widget category so all 9 section widgets group
 * together in the Elementor panel.
 *
 * @param \Elementor\Elements_Manager $elements_manager
 * @return void
 */
function rentiva_register_elementor_category( $elements_manager ) {
	$elements_manager->add_category(
		'rentiva',
		array(
			'title' => __( 'Rentiva', 'rentiva' ),
			'icon'  => 'eicon-theme-builder',
		)
	);
}
add_action( 'elementor/elements/categories_registered', 'rentiva_register_elementor_category' );

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

/**
 * Register all 9 homepage-section widgets.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager
 * @return void
 */
function rentiva_register_elementor_widgets( $widgets_manager ) {
	$widgets_manager->register( new Rentiva_Elementor_Widget_Hero() );
	$widgets_manager->register( new Rentiva_Elementor_Widget_Trust_Strip() );
	$widgets_manager->register( new Rentiva_Elementor_Widget_Categories() );
	$widgets_manager->register( new Rentiva_Elementor_Widget_Popular_Rentals() );
	$widgets_manager->register( new Rentiva_Elementor_Widget_Promo_Banner() );
	$widgets_manager->register( new Rentiva_Elementor_Widget_How_It_Works() );
	$widgets_manager->register( new Rentiva_Elementor_Widget_Why_Rentiva() );
	$widgets_manager->register( new Rentiva_Elementor_Widget_Testimonial() );
	$widgets_manager->register( new Rentiva_Elementor_Widget_Final_Cta() );
}
add_action( 'elementor/widgets/register', 'rentiva_register_elementor_widgets' );
