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
 * Base class for every Rentiva homepage-section widget: content (copy,
 * images) is fixed to match the mockup and is edited via Rentiva → Theme
 * Settings, not per-widget — but each section's text elements DO get a
 * Style-tab "Text Color" + "Typography" control per element, wired through
 * Elementor's own `selectors` engine (see add_text_style_section() below),
 * so an admin can restyle a section's look directly in the Elementor panel
 * without editing CSS.
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
	 * Add one Style-tab section with a Text Color control and a Typography
	 * group control (font family/size/weight/line-height/letter-spacing),
	 * both scoped to $selector via Elementor's `selectors` mechanism —
	 * Elementor generates the resulting CSS itself, scoped to this exact
	 * widget instance via `{{WRAPPER}}`, and writes it into the page's own
	 * generated stylesheet. Nothing in render() or the template-parts needs
	 * to change for these controls to take effect, and since `{{WRAPPER}}`
	 * always resolves to this specific widget's own unique element class,
	 * this is safe to use even for selectors built on classes shared across
	 * multiple sections (`.rentiva-h2`, `.rentiva-lead`, etc.) — the override
	 * never leaks into other widgets or the built-in PHP fallback layout.
	 *
	 * @param string $key      Unique control-id prefix (e.g. 'heading').
	 * @param string $label    Style section label shown in the Elementor panel.
	 * @param string $selector CSS selector, relative to the widget root, to style.
	 * @return void
	 */
	protected function add_text_style_section( $key, $label, $selector ) {
		$this->start_controls_section(
			'style_section_' . $key,
			array(
				'label' => $label,
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			$key . '_color',
			array(
				'label'     => __( 'Text Color', 'rentiva' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $selector => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => $key . '_typography',
				'selector' => '{{WRAPPER}} ' . $selector,
			)
		);

		$this->end_controls_section();
	}

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

	protected function register_controls() {
		$this->add_text_style_section( 'eyebrow', __( 'Eyebrow', 'rentiva' ), '.rentiva-hero__eyebrow' );
		$this->add_text_style_section( 'heading', __( 'Headline', 'rentiva' ), '.rentiva-hero-title' );
		$this->add_text_style_section( 'accent', __( 'Headline Accent Word', 'rentiva' ), '.rentiva-hero-title__accent' );
		$this->add_text_style_section( 'subheading', __( 'Subheading', 'rentiva' ), '.rentiva-hero__subtitle' );
	}
}

class Rentiva_Elementor_Widget_Trust_Strip extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-trust-strip'; }
	public function get_title() { return __( 'Rentiva: Trust Strip', 'rentiva' ); }
	public function get_icon() { return 'eicon-counter'; }
	protected function get_section_slug() { return 'trust-strip'; }
	// No dedicated trust-strip.css — its styles live bundled in home.css.
	protected function get_style_handle() { return 'rentiva-home'; }

	protected function register_controls() {
		$this->add_text_style_section( 'value', __( 'Stat Value', 'rentiva' ), '.rentiva-trust-strip__value' );
		$this->add_text_style_section( 'label', __( 'Stat Label', 'rentiva' ), '.rentiva-trust-strip__label' );
	}
}

class Rentiva_Elementor_Widget_Categories extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-categories'; }
	public function get_title() { return __( 'Rentiva: Categories', 'rentiva' ); }
	public function get_icon() { return 'eicon-gallery-grid'; }
	protected function get_section_slug() { return 'categories'; }

	protected function register_controls() {
		$this->add_text_style_section( 'heading', __( 'Headline', 'rentiva' ), '.rentiva-h2' );
		$this->add_text_style_section( 'lead', __( 'Subheading', 'rentiva' ), '.rentiva-lead' );
	}
}

class Rentiva_Elementor_Widget_Popular_Rentals extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-popular-rentals'; }
	public function get_title() { return __( 'Rentiva: Popular Rentals', 'rentiva' ); }
	public function get_icon() { return 'eicon-product-related'; }
	protected function get_section_slug() { return 'popular-rentals'; }

	protected function register_controls() {
		$this->add_text_style_section( 'heading', __( 'Headline', 'rentiva' ), '.rentiva-h2' );
		$this->add_text_style_section( 'lead', __( 'Subheading', 'rentiva' ), '.rentiva-lead' );
	}
}

class Rentiva_Elementor_Widget_Promo_Banner extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-promo-banner'; }
	public function get_title() { return __( 'Rentiva: Promo Banner', 'rentiva' ); }
	public function get_icon() { return 'eicon-banner'; }
	protected function get_section_slug() { return 'promo-banner'; }

	protected function register_controls() {
		$this->add_text_style_section( 'badge', __( 'Badge', 'rentiva' ), '.rentiva-promo-banner__badge' );
		$this->add_text_style_section( 'heading', __( 'Headline', 'rentiva' ), '.rentiva-promo-banner__title' );
		$this->add_text_style_section( 'text', __( 'Body Text', 'rentiva' ), '.rentiva-promo-banner__text' );
	}
}

class Rentiva_Elementor_Widget_How_It_Works extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-how-it-works'; }
	public function get_title() { return __( 'Rentiva: How It Works', 'rentiva' ); }
	public function get_icon() { return 'eicon-number-field'; }
	protected function get_section_slug() { return 'how-it-works'; }

	protected function register_controls() {
		$this->add_text_style_section( 'heading', __( 'Headline', 'rentiva' ), '.rentiva-h2' );
		$this->add_text_style_section( 'number', __( 'Step Number', 'rentiva' ), '.rentiva-how-it-works__number' );
		$this->add_text_style_section( 'step_title', __( 'Step Title', 'rentiva' ), '.rentiva-h4' );
		$this->add_text_style_section( 'step_body', __( 'Step Description', 'rentiva' ), '.rentiva-body' );
	}
}

class Rentiva_Elementor_Widget_Why_Rentiva extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-why-rentiva'; }
	public function get_title() { return __( 'Rentiva: Why Rentiva', 'rentiva' ); }
	public function get_icon() { return 'eicon-check-circle-o'; }
	protected function get_section_slug() { return 'why-rentiva'; }

	protected function register_controls() {
		$this->add_text_style_section( 'eyebrow', __( 'Eyebrow', 'rentiva' ), '.rentiva-eyebrow' );
		$this->add_text_style_section( 'heading', __( 'Headline', 'rentiva' ), '.rentiva-why-rentiva__title' );
		$this->add_text_style_section( 'feature_title', __( 'Feature Title', 'rentiva' ), '.rentiva-why-rentiva__feature-title' );
		$this->add_text_style_section( 'feature_desc', __( 'Feature Description', 'rentiva' ), '.rentiva-why-rentiva__feature-desc' );
	}
}

class Rentiva_Elementor_Widget_Testimonial extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-testimonial'; }
	public function get_title() { return __( 'Rentiva: Testimonial', 'rentiva' ); }
	public function get_icon() { return 'eicon-testimonial'; }
	protected function get_section_slug() { return 'testimonial'; }

	protected function register_controls() {
		$this->add_text_style_section( 'quote', __( 'Quote', 'rentiva' ), '.rentiva-testimonial__quote' );
		$this->add_text_style_section( 'name', __( 'Author Name', 'rentiva' ), '.rentiva-testimonial__name' );
		$this->add_text_style_section( 'role', __( 'Author Role', 'rentiva' ), '.rentiva-testimonial__role' );
	}
}

class Rentiva_Elementor_Widget_Final_Cta extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-final-cta'; }
	public function get_title() { return __( 'Rentiva: Final CTA', 'rentiva' ); }
	public function get_icon() { return 'eicon-call-to-action'; }
	protected function get_section_slug() { return 'final-cta'; }

	protected function register_controls() {
		$this->add_text_style_section( 'heading', __( 'Headline', 'rentiva' ), '.rentiva-h1' );
		$this->add_text_style_section( 'lead', __( 'Subheading', 'rentiva' ), '.rentiva-lead' );
	}
}
