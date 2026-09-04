<?php
/**
 * WooCommerce integration — styling/container alignment ONLY.
 *
 * WooCommerce here exists purely as RBFW's checkout/order engine (every
 * `rbfw_item` gets an auto-created hidden shadow product — see
 * docs/booking-integration.md). This file never touches cart, checkout,
 * pricing, or catalog-visibility logic: the plugin already fully owns
 * hiding/404ing its own shadow products, so there is nothing for the theme
 * to filter there.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! rentiva_has_woocommerce() ) {
	return;
}

/**
 * Declare WooCommerce support with a gallery/grid config matching the
 * theme's own card system.
 *
 * @return void
 */
function rentiva_woocommerce_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'gallery_thumbnail_image_width' => 150,
			'single_image_width'    => 1200,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 3,
				'min_columns'     => 1,
				'max_columns'     => 4,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'rentiva_woocommerce_setup' );

/**
 * Wrap WooCommerce's own template output in the theme's container so cart/
 * checkout/my-account pages line up with the rest of the site.
 *
 * @return void
 */
function rentiva_woocommerce_wrapper_start() {
	echo '<div class="rentiva-container rentiva-section--tight rentiva-woocommerce">';
}
add_action( 'woocommerce_before_main_content', 'rentiva_woocommerce_wrapper_start', 10 );

/**
 * @return void
 */
function rentiva_woocommerce_wrapper_end() {
	echo '</div>';
}
add_action( 'woocommerce_after_main_content', 'rentiva_woocommerce_wrapper_end', 10 );

// The theme's own page header/breadcrumb styling replaces WooCommerce's defaults.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

// Related/upsell product columns match the theme's 3-column archive grid.
add_filter( 'woocommerce_upsell_display_args', function ( $args ) {
	$args['columns'] = 3;
	return $args;
} );
add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
	$args['posts_per_page'] = 3;
	$args['columns']        = 3;
	return $args;
} );
