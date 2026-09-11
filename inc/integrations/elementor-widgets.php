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
	 * A `Controls_Manager::MEDIA` control's `default`, so opening a widget
	 * that already has a real Theme Settings image pre-fills the picker
	 * with it — same reasoning as rentiva_get_default_trust_stats() (see
	 * docs/elementor.md): an unset `default` leaves the field looking
	 * "empty" even though the section has a live photo, which reads as
	 * "not editable". Returns [] (no default) when there's no image to
	 * show, since there's no sensible placeholder image asset to offer.
	 *
	 * @param int $attachment_id
	 * @return array{id?:int,url?:string}
	 */
	protected function media_default( $attachment_id ) {
		$attachment_id = (int) $attachment_id;
		if ( ! $attachment_id ) {
			return array();
		}
		return array(
			'id'  => $attachment_id,
			'url' => wp_get_attachment_url( $attachment_id ),
		);
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

	/**
	 * Every piece of hero copy is a real Content-tab field, each optional —
	 * a blank field falls back to Rentiva → Theme Settings (or that
	 * setting's own hardcoded default) via template-parts/home/hero.php's
	 * $rentiva_arg() helper, so leaving everything blank reproduces the
	 * exact same hero the built-in fallback layout renders.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Hero Content', 'rentiva' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'eyebrow',
			array(
				'label'   => __( 'Eyebrow', 'rentiva' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => rentiva_get_setting( 'hero_eyebrow', __( 'RENT • RIDE • EXPLORE', 'rentiva' ) ),
			)
		);
		$this->add_control(
			'title',
			array(
				'label'       => __( 'Headline', 'rentiva' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => rentiva_get_setting( 'hero_title', __( 'Rent. Ride. Explore.', 'rentiva' ) ),
				'description' => __( 'The last word is automatically shown in the accent color.', 'rentiva' ),
			)
		);
		$this->add_control(
			'subtitle',
			array(
				'label'       => __( 'Subheading', 'rentiva' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => rentiva_get_setting( 'hero_subtitle', __( 'Premium bikes, gear and equipment — ready whenever you are.', 'rentiva' ) ),
				'description' => __( 'Breaks onto a second line at an em dash (—), if present.', 'rentiva' ),
			)
		);

		$this->add_control(
			'image',
			array(
				'label'     => __( 'Background Photo', 'rentiva' ),
				'type'      => \Elementor\Controls_Manager::MEDIA,
				'default'   => $this->media_default( rentiva_get_setting( 'hero_image_id', 0 ) ),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'heading_badges',
			array(
				'label'     => __( 'Trust Badges', 'rentiva' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_control( 'badge1_value', array( 'label' => __( 'Badge 1 Value', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '4.9 / 5' ) );
		$this->add_control( 'badge1_label', array( 'label' => __( 'Badge 1 Label', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Top Rated', 'rentiva' ) ) );
		$this->add_control( 'badge2_value', array( 'label' => __( 'Badge 2 Value', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '10,000+' ) );
		$this->add_control( 'badge2_label', array( 'label' => __( 'Badge 2 Label', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Rentals Done', 'rentiva' ) ) );
		$this->add_control( 'live_text', array( 'label' => __( 'Live Availability Text', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Available Now', 'rentiva' ) ) );

		$this->add_control(
			'heading_buttons',
			array(
				'label'     => __( 'Buttons', 'rentiva' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_control( 'cta_primary_text', array( 'label' => __( 'Primary Button Text', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Explore Rentals', 'rentiva' ) ) );
		$this->add_control( 'cta_primary_url', array( 'label' => __( 'Primary Button Link', 'rentiva' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => rentiva_get_rentals_page_url() ), 'show_external' => false ) );
		$this->add_control( 'cta_secondary_text', array( 'label' => __( 'Secondary Button Text', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'List Your Equipment', 'rentiva' ) ) );
		$this->add_control( 'cta_secondary_url', array( 'label' => __( 'Secondary Button Link', 'rentiva' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => rentiva_get_list_item_url() ), 'show_external' => false ) );

		$this->add_control(
			'heading_search',
			array(
				'label'     => __( 'Search Panel', 'rentiva' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_control( 'search_label', array( 'label' => __( 'Panel Label', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'FIND YOUR RENTAL', 'rentiva' ) ) );
		$this->add_control( 'field_where_label', array( 'label' => __( '"Item Name" Field Label', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Item Name', 'rentiva' ) ) );
		$this->add_control( 'field_where_placeholder', array( 'label' => __( '"Item Name" Field Placeholder', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Search item name…', 'rentiva' ) ) );
		$this->add_control( 'field_pickup_label', array( 'label' => __( '"Pickup" Field Label', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Pickup', 'rentiva' ) ) );
		$this->add_control( 'field_return_label', array( 'label' => __( '"Return" Field Label', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Return', 'rentiva' ) ) );
		$this->add_control( 'field_category_label', array( 'label' => __( '"Category" Field Label', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Category', 'rentiva' ) ) );
		$this->add_control( 'submit_text', array( 'label' => __( 'Submit Button Text', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Find Rentals', 'rentiva' ) ) );

		$this->end_controls_section();

		$this->add_text_style_section( 'eyebrow', __( 'Eyebrow', 'rentiva' ), '.rentiva-hero__eyebrow' );
		$this->add_text_style_section( 'heading', __( 'Headline', 'rentiva' ), '.rentiva-hero-title' );
		$this->add_text_style_section( 'accent', __( 'Headline Accent Word', 'rentiva' ), '.rentiva-hero-title__accent' );
		$this->add_text_style_section( 'subheading', __( 'Subheading', 'rentiva' ), '.rentiva-hero__subtitle' );
	}

	/**
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$args = array(
			'image_id'                => ! empty( $settings['image']['id'] ) ? (int) $settings['image']['id'] : 0,
			'eyebrow'                 => $settings['eyebrow'],
			'title'                   => $settings['title'],
			'subtitle'                => $settings['subtitle'],
			'badge1_value'            => $settings['badge1_value'],
			'badge1_label'            => $settings['badge1_label'],
			'badge2_value'            => $settings['badge2_value'],
			'badge2_label'            => $settings['badge2_label'],
			'live_text'               => $settings['live_text'],
			'cta_primary_text'        => $settings['cta_primary_text'],
			'cta_primary_url'         => ! empty( $settings['cta_primary_url']['url'] ) ? $settings['cta_primary_url']['url'] : '',
			'cta_secondary_text'      => $settings['cta_secondary_text'],
			'cta_secondary_url'       => ! empty( $settings['cta_secondary_url']['url'] ) ? $settings['cta_secondary_url']['url'] : '',
			'search_label'            => $settings['search_label'],
			'field_where_label'       => $settings['field_where_label'],
			'field_where_placeholder' => $settings['field_where_placeholder'],
			'field_pickup_label'      => $settings['field_pickup_label'],
			'field_return_label'      => $settings['field_return_label'],
			'field_category_label'    => $settings['field_category_label'],
			'submit_text'             => $settings['submit_text'],
		);

		rentiva_template_part( 'template-parts/home/hero', '', $args );
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
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Trust Strip Content', 'rentiva' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'value', array( 'label' => __( 'Value', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => '10,000+' ) );
		$repeater->add_control( 'label', array( 'label' => __( 'Label', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => __( 'rentals completed', 'rentiva' ) ) );

		$this->add_control(
			'stats',
			array(
				'label'       => __( 'Stats', 'rentiva' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ value }}} — {{{ label }}}',
				'default'     => rentiva_get_default_trust_stats(),
				'description' => __( 'Pre-filled with what\'s live now (from Rentiva → Theme Settings). Edit, add, remove, or reorder rows — saving this widget makes these 4 rows this page\'s own stats from then on, independent of Theme Settings.', 'rentiva' ),
			)
		);

		$this->end_controls_section();

		$this->add_text_style_section( 'value', __( 'Stat Value', 'rentiva' ), '.rentiva-trust-strip__value' );
		$this->add_text_style_section( 'label', __( 'Stat Label', 'rentiva' ), '.rentiva-trust-strip__label' );
	}

	/**
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$args     = array();
		if ( ! empty( $settings['stats'] ) ) {
			$args['stats'] = $settings['stats'];
		}
		rentiva_template_part( 'template-parts/home/trust-strip', '', $args );
	}
}

class Rentiva_Elementor_Widget_Categories extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-categories'; }
	public function get_title() { return __( 'Rentiva: Categories', 'rentiva' ); }
	public function get_icon() { return 'eicon-gallery-grid'; }
	protected function get_section_slug() { return 'categories'; }

	/**
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Categories Content', 'rentiva' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control( 'heading', array( 'label' => __( 'Headline', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Explore What You Need', 'rentiva' ) ) );
		$this->add_control( 'lead', array( 'label' => __( 'Subheading', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'Find the right equipment for your next adventure.', 'rentiva' ) ) );
		$this->add_control( 'link_text', array( 'label' => __( 'Link Text', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'View all categories', 'rentiva' ) ) );
		$this->add_control( 'link_url', array( 'label' => __( 'Link URL', 'rentiva' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => rentiva_get_rentals_page_url() ), 'show_external' => false ) );

		$category_repeater = new \Elementor\Repeater();
		$category_repeater->add_control(
			'term_id',
			array(
				'label'       => __( 'Category', 'rentiva' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'options'     => rentiva_get_category_picker_options(),
			)
		);
		$this->add_control(
			'categories',
			array(
				'label'       => __( 'Categories', 'rentiva' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $category_repeater->get_controls(),
				'default'     => rentiva_get_default_category_repeater_rows(),
				'description' => __( 'Pre-filled with the 6 categories shown now. Edit, add, remove, or reorder rows to control exactly which categories appear here and in what order. Each category\'s own name/image is still edited on that category\'s own admin screen.', 'rentiva' ),
			)
		);

		$this->end_controls_section();

		$this->add_text_style_section( 'heading', __( 'Headline', 'rentiva' ), '.rentiva-h2' );
		$this->add_text_style_section( 'lead', __( 'Subheading', 'rentiva' ), '.rentiva-lead' );
	}

	/**
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$args = array(
			'heading'   => $settings['heading'],
			'lead'      => $settings['lead'],
			'link_text' => $settings['link_text'],
			'link_url'  => ! empty( $settings['link_url']['url'] ) ? $settings['link_url']['url'] : '',
		);

		if ( ! empty( $settings['categories'] ) ) {
			$cards = array();
			foreach ( $settings['categories'] as $rentiva_row ) {
				$card = ! empty( $rentiva_row['term_id'] ) ? rentiva_get_category_card( $rentiva_row['term_id'] ) : null;
				if ( $card ) {
					$cards[] = $card;
				}
			}
			if ( ! empty( $cards ) ) {
				$args['cards'] = $cards;
			}
		}

		rentiva_template_part( 'template-parts/home/categories', '', $args );
	}
}

class Rentiva_Elementor_Widget_Popular_Rentals extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-popular-rentals'; }
	public function get_title() { return __( 'Rentiva: Popular Rentals', 'rentiva' ); }
	public function get_icon() { return 'eicon-product-related'; }
	protected function get_section_slug() { return 'popular-rentals'; }

	/**
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Popular Rentals Content', 'rentiva' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control( 'heading', array( 'label' => __( 'Headline', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Popular Rentals', 'rentiva' ) ) );
		$this->add_control( 'lead', array( 'label' => __( 'Subheading', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'Highly rated equipment ready for your next adventure.', 'rentiva' ) ) );
		$this->add_control( 'link_text', array( 'label' => __( 'Link Text', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'View all rentals', 'rentiva' ) ) );
		$this->add_control( 'link_url', array( 'label' => __( 'Link URL', 'rentiva' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => rentiva_get_rentals_page_url() ), 'show_external' => false ) );

		$item_repeater = new \Elementor\Repeater();
		$item_repeater->add_control(
			'item_id',
			array(
				'label'       => __( 'Rental Item', 'rentiva' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'options'     => rentiva_get_rental_item_picker_options(),
			)
		);
		$this->add_control(
			'items',
			array(
				'label'       => __( 'Rental Items', 'rentiva' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $item_repeater->get_controls(),
				'default'     => rentiva_get_default_rental_item_repeater_rows(),
				'description' => __( 'Pre-filled with the items shown now. Edit, add, remove, or reorder rows to control exactly which rentals appear here and in what order. Each item\'s own title/photo/price is still edited on that item itself.', 'rentiva' ),
			)
		);

		$this->end_controls_section();

		$this->add_text_style_section( 'heading', __( 'Headline', 'rentiva' ), '.rentiva-h2' );
		$this->add_text_style_section( 'lead', __( 'Subheading', 'rentiva' ), '.rentiva-lead' );
	}

	/**
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$args = array(
			'heading'   => $settings['heading'],
			'lead'      => $settings['lead'],
			'link_text' => $settings['link_text'],
			'link_url'  => ! empty( $settings['link_url']['url'] ) ? $settings['link_url']['url'] : '',
		);

		if ( ! empty( $settings['items'] ) && class_exists( 'Rentiva_Rental_Adapter' ) ) {
			$items = array();
			foreach ( $settings['items'] as $rentiva_row ) {
				if ( empty( $rentiva_row['item_id'] ) ) {
					continue;
				}
				$item_id = (int) $rentiva_row['item_id'];
				if ( 'publish' !== get_post_status( $item_id ) ) {
					continue;
				}
				$items[] = Rentiva_Rental_Adapter::get_item_card_data( $item_id );
			}
			if ( ! empty( $items ) ) {
				$args['items'] = $items;
			}
		}

		rentiva_template_part( 'template-parts/home/popular-rentals', '', $args );
	}
}

class Rentiva_Elementor_Widget_Promo_Banner extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-promo-banner'; }
	public function get_title() { return __( 'Rentiva: Promo Banner', 'rentiva' ); }
	public function get_icon() { return 'eicon-banner'; }
	protected function get_section_slug() { return 'promo-banner'; }

	/**
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Promo Banner Content', 'rentiva' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control( 'image', array( 'label' => __( 'Background Photo', 'rentiva' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => $this->media_default( rentiva_get_setting( 'promo_image_id', 0 ) ) ) );
		$this->add_control( 'badge', array( 'label' => __( 'Badge', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => rentiva_get_setting( 'promo_badge', __( 'WEEKEND SPECIAL · UP TO 20% OFF', 'rentiva' ) ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Headline', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => rentiva_get_setting( 'promo_title', __( 'Your next adventure starts here.', 'rentiva' ) ) ) );
		$this->add_control( 'text', array( 'label' => __( 'Body Text', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => rentiva_get_setting( 'promo_text', __( 'Discover premium equipment from trusted local owners.', 'rentiva' ) ) ) );
		$this->add_control( 'cta_text', array( 'label' => __( 'Button Text', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Explore Rentals', 'rentiva' ) ) );
		$this->add_control( 'cta_url', array( 'label' => __( 'Button Link', 'rentiva' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => rentiva_get_rentals_page_url() ), 'show_external' => false ) );
		$this->end_controls_section();

		$this->start_controls_section(
			'style_background_section',
			array(
				'label' => __( 'Background', 'rentiva' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_control(
			'background_color',
			array(
				'label'       => __( 'Background Color', 'rentiva' ),
				'type'        => \Elementor\Controls_Manager::COLOR,
				'description' => __( 'Shows behind/around the photo (or on its own if no photo is set).', 'rentiva' ),
				'selectors'   => array(
					'{{WRAPPER}} .rentiva-promo-banner__media' => 'background-color: {{VALUE}};',
				),
			)
		);
		$this->add_control(
			'overlay_color',
			array(
				'label'       => __( 'Photo Overlay Color', 'rentiva' ),
				'type'        => \Elementor\Controls_Manager::COLOR,
				'description' => __( 'The tint over the photo that keeps the text readable.', 'rentiva' ),
				'selectors'   => array(
					'{{WRAPPER}} .rentiva-promo-banner__gradient' => 'background: {{VALUE}};',
				),
			)
		);
		$this->end_controls_section();

		$this->add_text_style_section( 'badge', __( 'Badge', 'rentiva' ), '.rentiva-promo-banner__badge' );
		$this->add_text_style_section( 'heading', __( 'Headline', 'rentiva' ), '.rentiva-promo-banner__title' );
		$this->add_text_style_section( 'text', __( 'Body Text', 'rentiva' ), '.rentiva-promo-banner__text' );
	}

	/**
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		rentiva_template_part(
			'template-parts/home/promo-banner',
			'',
			array(
				'image_id' => ! empty( $settings['image']['id'] ) ? (int) $settings['image']['id'] : 0,
				'badge'    => $settings['badge'],
				'title'    => $settings['title'],
				'text'     => $settings['text'],
				'cta_text' => $settings['cta_text'],
				'cta_url'  => ! empty( $settings['cta_url']['url'] ) ? $settings['cta_url']['url'] : '',
			)
		);
	}
}

class Rentiva_Elementor_Widget_How_It_Works extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-how-it-works'; }
	public function get_title() { return __( 'Rentiva: How It Works', 'rentiva' ); }
	public function get_icon() { return 'eicon-number-field'; }
	protected function get_section_slug() { return 'how-it-works'; }

	/**
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'How It Works Content', 'rentiva' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control( 'heading', array( 'label' => __( 'Headline', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Rent in 3 Simple Steps', 'rentiva' ) ) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'title', array( 'label' => __( 'Step Title', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => __( 'Find', 'rentiva' ) ) );
		$repeater->add_control( 'desc', array( 'label' => __( 'Step Description', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'placeholder' => __( 'Discover the perfect equipment near you.', 'rentiva' ) ) );

		$this->add_control(
			'steps',
			array(
				'label'       => __( 'Steps', 'rentiva' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => rentiva_get_default_how_it_works_steps(),
				'description' => __( 'Pre-filled with the current Find/Book/Enjoy steps. Edit, add, remove, or reorder rows — the step number always follows the row\'s position.', 'rentiva' ),
			)
		);
		$this->end_controls_section();

		$this->add_text_style_section( 'heading', __( 'Headline', 'rentiva' ), '.rentiva-h2' );
		$this->add_text_style_section( 'number', __( 'Step Number', 'rentiva' ), '.rentiva-how-it-works__number' );
		$this->add_text_style_section( 'step_title', __( 'Step Title', 'rentiva' ), '.rentiva-h4' );
		$this->add_text_style_section( 'step_body', __( 'Step Description', 'rentiva' ), '.rentiva-body' );
	}

	/**
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$args     = array( 'heading' => $settings['heading'] );
		if ( ! empty( $settings['steps'] ) ) {
			$args['steps'] = $settings['steps'];
		}
		rentiva_template_part( 'template-parts/home/how-it-works', '', $args );
	}
}

class Rentiva_Elementor_Widget_Why_Rentiva extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-why-rentiva'; }
	public function get_title() { return __( 'Rentiva: Why Rentiva', 'rentiva' ); }
	public function get_icon() { return 'eicon-check-circle-o'; }
	protected function get_section_slug() { return 'why-rentiva'; }

	/**
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Why Rentiva Content', 'rentiva' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control( 'image', array( 'label' => __( 'Photo', 'rentiva' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => $this->media_default( rentiva_get_setting( 'why_image_id', 0 ) ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'WHY RENTIVA', 'rentiva' ) ) );
		$this->add_control( 'heading', array( 'label' => __( 'Headline', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Everything you need for a better rental.', 'rentiva' ) ) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'title', array( 'label' => __( 'Feature Title', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => __( 'Verified Equipment', 'rentiva' ) ) );
		$repeater->add_control( 'desc', array( 'label' => __( 'Feature Description', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'placeholder' => __( 'Every item is reviewed and quality checked.', 'rentiva' ) ) );

		$this->add_control(
			'features',
			array(
				'label'       => __( 'Features', 'rentiva' ),
				'type'        => \Elementor\Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => rentiva_get_default_why_rentiva_features(),
				'description' => __( 'Pre-filled with the current 4 features. Edit, add, remove, or reorder rows.', 'rentiva' ),
			)
		);

		$this->add_control( 'link_text', array( 'label' => __( 'Link Text', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Learn More', 'rentiva' ) ) );
		$this->add_control( 'link_url', array( 'label' => __( 'Link URL', 'rentiva' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => rentiva_get_rentals_page_url() ), 'show_external' => false ) );
		$this->end_controls_section();

		$this->add_text_style_section( 'eyebrow', __( 'Eyebrow', 'rentiva' ), '.rentiva-eyebrow' );
		$this->add_text_style_section( 'heading', __( 'Headline', 'rentiva' ), '.rentiva-why-rentiva__title' );
		$this->add_text_style_section( 'feature_title', __( 'Feature Title', 'rentiva' ), '.rentiva-why-rentiva__feature-title' );
		$this->add_text_style_section( 'feature_desc', __( 'Feature Description', 'rentiva' ), '.rentiva-why-rentiva__feature-desc' );
	}

	/**
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$args     = array(
			'image_id'  => ! empty( $settings['image']['id'] ) ? (int) $settings['image']['id'] : 0,
			'eyebrow'   => $settings['eyebrow'],
			'heading'   => $settings['heading'],
			'link_text' => $settings['link_text'],
			'link_url'  => ! empty( $settings['link_url']['url'] ) ? $settings['link_url']['url'] : '',
		);
		if ( ! empty( $settings['features'] ) ) {
			$args['features'] = $settings['features'];
		}
		rentiva_template_part( 'template-parts/home/why-rentiva', '', $args );
	}
}

class Rentiva_Elementor_Widget_Testimonial extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-testimonial'; }
	public function get_title() { return __( 'Rentiva: Testimonial', 'rentiva' ); }
	public function get_icon() { return 'eicon-testimonial'; }
	protected function get_section_slug() { return 'testimonial'; }

	/**
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Testimonial Content', 'rentiva' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control( 'quote', array( 'label' => __( 'Quote', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => rentiva_get_setting( 'testimonial_quote', __( 'The easiest rental experience I\'ve ever had. The bike was perfect and the entire process took less than two minutes.', 'rentiva' ) ) ) );
		$this->add_control( 'avatar', array( 'label' => __( 'Avatar Photo', 'rentiva' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => $this->media_default( rentiva_get_setting( 'testimonial_avatar_id', 0 ) ) ) );
		$this->add_control( 'name', array( 'label' => __( 'Author Name', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => rentiva_get_setting( 'testimonial_name', __( 'Daniel Morgan', 'rentiva' ) ) ) );
		$this->add_control( 'role', array( 'label' => __( 'Author Role', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => rentiva_get_setting( 'testimonial_role', __( 'Weekend Traveler', 'rentiva' ) ) ) );
		$this->end_controls_section();

		$this->add_text_style_section( 'quote', __( 'Quote', 'rentiva' ), '.rentiva-testimonial__quote' );
		$this->add_text_style_section( 'name', __( 'Author Name', 'rentiva' ), '.rentiva-testimonial__name' );
		$this->add_text_style_section( 'role', __( 'Author Role', 'rentiva' ), '.rentiva-testimonial__role' );
	}

	/**
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		rentiva_template_part(
			'template-parts/home/testimonial',
			'',
			array(
				'quote'     => $settings['quote'],
				'avatar_id' => ! empty( $settings['avatar']['id'] ) ? (int) $settings['avatar']['id'] : 0,
				'name'      => $settings['name'],
				'role'      => $settings['role'],
			)
		);
	}
}

class Rentiva_Elementor_Widget_Final_Cta extends Rentiva_Elementor_Section_Widget {
	public function get_name() { return 'rentiva-final-cta'; }
	public function get_title() { return __( 'Rentiva: Final CTA', 'rentiva' ); }
	public function get_icon() { return 'eicon-call-to-action'; }
	protected function get_section_slug() { return 'final-cta'; }

	/**
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Final CTA Content', 'rentiva' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control( 'heading', array( 'label' => __( 'Headline', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Ready to explore?', 'rentiva' ) ) );
		$this->add_control( 'lead', array( 'label' => __( 'Subheading', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => __( 'Find your perfect rental and start your next adventure.', 'rentiva' ) ) );
		$this->add_control(
			'heading_buttons',
			array(
				'label'     => __( 'Buttons', 'rentiva' ),
				'type'      => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);
		$this->add_control( 'cta_primary_text', array( 'label' => __( 'Primary Button Text', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'Explore Rentals', 'rentiva' ) ) );
		$this->add_control( 'cta_primary_url', array( 'label' => __( 'Primary Button Link', 'rentiva' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => rentiva_get_rentals_page_url() ), 'show_external' => false ) );
		$this->add_control( 'cta_secondary_text', array( 'label' => __( 'Secondary Button Text', 'rentiva' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => __( 'List Your Item', 'rentiva' ) ) );
		$this->add_control( 'cta_secondary_url', array( 'label' => __( 'Secondary Button Link', 'rentiva' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => rentiva_get_list_item_url() ), 'show_external' => false ) );
		$this->end_controls_section();

		$this->add_text_style_section( 'heading', __( 'Headline', 'rentiva' ), '.rentiva-h1' );
		$this->add_text_style_section( 'lead', __( 'Subheading', 'rentiva' ), '.rentiva-lead' );
	}

	/**
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		rentiva_template_part(
			'template-parts/home/final-cta',
			'',
			array(
				'heading'            => $settings['heading'],
				'lead'               => $settings['lead'],
				'cta_primary_text'   => $settings['cta_primary_text'],
				'cta_primary_url'    => ! empty( $settings['cta_primary_url']['url'] ) ? $settings['cta_primary_url']['url'] : '',
				'cta_secondary_text' => $settings['cta_secondary_text'],
				'cta_secondary_url'  => ! empty( $settings['cta_secondary_url']['url'] ) ? $settings['cta_secondary_url']['url'] : '',
			)
		);
	}
}
