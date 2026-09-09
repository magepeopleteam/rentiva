<?php
/**
 * Elementor integration — one widget per homepage section (hero, categories,
 * popular rentals, etc.), defined in inc/integrations/elementor-widgets.php.
 * Elementor is a hard theme dependency (see style.css's "Requires Plugins"
 * header): on activation, Rentiva builds the "Homepage" page out of exactly
 * these 9 widgets and sets it as the static front page (see
 * rentiva_setup_homepage_page() in inc/helpers.php), so the homepage is a
 * real, fully editable Elementor page from the first run. front-page.php's
 * built-in template-parts/home/*.php layout only remains as a fallback for
 * when that page is missing or Elementor isn't active. Each widget shares
 * the exact same render function as its template-part, so a page built with
 * them always looks identical to the fallback layout.
 *
 * Only plain functions live in this file — never a
 * `class X extends \Elementor\...`. PHP resolves a class's `extends` clause
 * the moment the file containing it is *parsed*, not merely executed, so
 * that class must live in its own file that's require()'d only from inside
 * a function body (see rentiva_register_elementor_widgets() below) — never
 * at this file's top level, even wrapped in an `if`, since PHP compiles a
 * whole file (every branch) as one unit before running any of it.
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
 * Register all 9 homepage-section widgets. `elementor/widgets/register`
 * only ever fires once Elementor itself is fully loaded, so it's the one
 * safe place to require the file that declares classes extending
 * \Elementor\Widget_Base.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager
 * @return void
 */
function rentiva_register_elementor_widgets( $widgets_manager ) {
	require_once RENTIVA_DIR . 'inc/integrations/elementor-widgets.php';

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
