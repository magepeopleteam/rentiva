<?php
/**
 * Core theme setup: supports, menus, image sizes, content width.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme support flags and navigation menus.
 */
function rentiva_setup() {
	// Translations.
	load_theme_textdomain( 'rentiva', RENTIVA_DIR . 'languages' );

	// Core supports.
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support(
		'editor-color-palette',
		array(
			array( 'name' => __( 'Background', 'rentiva' ), 'slug' => 'background', 'color' => '#F9F8F5' ),
			array( 'name' => __( 'Foreground', 'rentiva' ), 'slug' => 'foreground', 'color' => '#141414' ),
			array( 'name' => __( 'Card', 'rentiva' ), 'slug' => 'card', 'color' => '#FFFFFF' ),
			array( 'name' => __( 'Muted', 'rentiva' ), 'slug' => 'muted', 'color' => '#F3F0EB' ),
			array( 'name' => __( 'Muted Foreground', 'rentiva' ), 'slug' => 'muted-foreground', 'color' => '#7A7670' ),
			array( 'name' => __( 'Border', 'rentiva' ), 'slug' => 'border', 'color' => '#E8E4DC' ),
			array( 'name' => __( 'Primary', 'rentiva' ), 'slug' => 'primary', 'color' => '#1B5E3B' ),
			array( 'name' => __( 'Primary Dark', 'rentiva' ), 'slug' => 'primary-dark', 'color' => '#154D2E' ),
		)
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 32,
			'width'       => 32,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support(
		'custom-background',
		array(
			'default-color' => 'F9F8F5',
		)
	);

	// Content width used by wp_img_tag_add_width_and_height_attr() etc.
	if ( ! isset( $GLOBALS['content_width'] ) ) {
		$GLOBALS['content_width'] = 1240;
	}

	// Navigation menus.
	register_nav_menus(
		array(
			'primary'        => __( 'Primary Navigation', 'rentiva' ),
			'footer-explore' => __( 'Footer — Explore', 'rentiva' ),
			'footer-company' => __( 'Footer — Company', 'rentiva' ),
			'footer-support' => __( 'Footer — Support', 'rentiva' ),
			'mobile'         => __( 'Mobile Navigation (optional override)', 'rentiva' ),
		)
	);

	// Image sizes used by the rental-card/gallery system and editorial content.
	// Aspect ratios are locked to mockup/rentiva.html's own image crops (or, where
	// a section has no direct mockup equivalent, to the exact box ratio its CSS
	// already declares) — object-fit: cover crops to whatever box ratio the CSS
	// enforces regardless of the source crop, so a mismatched source orientation
	// (e.g. a landscape crop forced into a portrait card) shows up as the image
	// looking oddly zoomed/cut, not just "the wrong size".
	add_image_size( 'rentiva-card', 600, 450, true );          // Popular Rentals / Similar Rentals card image.
	add_image_size( 'rentiva-category', 600, 700, true );      // Explore Categories tile (matches mockup's 6:7 crop).
	add_image_size( 'rentiva-hero', 1920, 1080, true );        // Homepage hero full-bleed photo.
	add_image_size( 'rentiva-promo', 1920, 900, true );        // Promo banner full-bleed photo.
	add_image_size( 'rentiva-why', 800, 1000, true );          // Why Rentiva media (matches its CSS's own 4:5 aspect-ratio).
	add_image_size( 'rentiva-gallery-main', 1200, 750, true ); // Single-item main gallery image (16:10).
	add_image_size( 'rentiva-gallery-thumb', 300, 225, true ); // Single-item gallery thumbnail strip.
	add_image_size( 'rentiva-square', 400, 400, true );        // Reviewer/testimonial avatars.
}
add_action( 'after_setup_theme', 'rentiva_setup' );

/**
 * Body classes used by header.css/single-item.css to switch layout without
 * re-querying plugin/WooCommerce state in every template.
 *
 * @param string[] $classes Existing body classes.
 * @return string[]
 */
function rentiva_body_classes( $classes ) {
	if ( ! rentiva_has_booking_plugin() ) {
		$classes[] = 'rentiva-no-booking-plugin';
	}
	if ( rentiva_has_woocommerce() ) {
		$classes[] = 'rentiva-has-woocommerce';
	}
	if ( is_front_page() && ! is_paged() ) {
		$classes[] = 'rentiva-front-page';
		// Only the homepage hero sits behind the fixed header with a full-bleed
		// photo — every other template needs the header solid from first paint.
		$classes[] = 'rentiva-header-on-media';
	}
	if ( rentiva_has_booking_plugin() && is_singular( 'rbfw_item' ) ) {
		$classes[] = 'rentiva-single-item';
	}
	return $classes;
}
add_filter( 'body_class', 'rentiva_body_classes' );

/**
 * Widen the default excerpt length slightly for rental description teasers.
 *
 * @return int
 */
function rentiva_excerpt_length() {
	return 24;
}
add_filter( 'excerpt_length', 'rentiva_excerpt_length', 999 );

/**
 * Use an ellipsis instead of the default "[&hellip;]" to match the editorial
 * voice used throughout the design ("View Details →").
 *
 * @return string
 */
function rentiva_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'rentiva_excerpt_more' );

/**
 * Target items per mega-menu column (before wrapping to the next column).
 * Hard-capped at 3 columns so the panel always fits the viewport.
 */
define( 'RENTIVA_MENU_ITEMS_PER_COLUMN', 8 );
define( 'RENTIVA_MENU_MAX_COLUMNS', 3 );

/**
 * Mark primary-nav parents with more than 8 direct children for multi-column
 * dropdowns. Adds `has-multi-column` and `menu-columns-{N}` on the parent <li>
 * so header.css can lay the submenu out as a mega-menu grid (desktop only).
 *
 * @param WP_Post[] $items Menu items.
 * @param stdClass  $args  wp_nav_menu() args.
 * @return WP_Post[]
 */
function rentiva_mark_multi_column_menu_items( $items, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $items;
	}

	$per_column   = (int) RENTIVA_MENU_ITEMS_PER_COLUMN;
	$max_columns  = (int) RENTIVA_MENU_MAX_COLUMNS;
	$child_counts = array();

	foreach ( $items as $item ) {
		$parent_id = (int) $item->menu_item_parent;
		if ( $parent_id > 0 ) {
			if ( ! isset( $child_counts[ $parent_id ] ) ) {
				$child_counts[ $parent_id ] = 0;
			}
			++$child_counts[ $parent_id ];
		}
	}

	foreach ( $items as $item ) {
		if ( 0 !== (int) $item->menu_item_parent ) {
			continue;
		}

		$count = isset( $child_counts[ (int) $item->ID ] ) ? (int) $child_counts[ (int) $item->ID ] : 0;
		if ( $count <= $per_column ) {
			continue;
		}

		$columns         = (int) ceil( $count / $per_column );
		$columns         = max( 2, min( $max_columns, $columns ) );
		$rows            = (int) ceil( $count / $columns );
		$item->classes[] = 'has-multi-column';
		$item->classes[] = 'menu-columns-' . $columns;
		$item->classes[] = 'menu-rows-' . $rows;
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'rentiva_mark_multi_column_menu_items', 10, 2 );
