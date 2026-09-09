<?php
/**
 * Conditional asset loading.
 *
 * Every stylesheet/script here is registered first (cheap) and only enqueued
 * on the templates that actually need it — booking/gallery/dashboard assets
 * never load globally.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Font loading: use locally bundled Plus Jakarta Sans + Inter if the site
 * owner has dropped woff2 files into assets/fonts/ (recommended before
 * ThemeForest submission — avoids a third-party request to Google Fonts for
 * GDPR/perf reasons), otherwise fall back to the Google Fonts CDN so the
 * theme still looks correct out of the box.
 *
 * @return void
 */
function rentiva_enqueue_fonts() {
	$local_font = RENTIVA_DIR . 'assets/fonts/rentiva-fonts.css';

	if ( is_readable( $local_font ) ) {
		wp_enqueue_style( 'rentiva-fonts', RENTIVA_URI . 'assets/fonts/rentiva-fonts.css', array(), RENTIVA_VERSION );
		return;
	}

	wp_enqueue_style(
		'rentiva-fonts',
		'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap',
		array(),
		null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- third-party URL; versioning it would break Google Fonts' own cache-busting.
	);
}

/**
 * Preconnect to Google Fonts only when we're actually using the CDN fallback.
 *
 * @return void
 */
function rentiva_resource_hints() {
	if ( is_readable( RENTIVA_DIR . 'assets/fonts/rentiva-fonts.css' ) ) {
		return;
	}
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}
add_action( 'wp_head', 'rentiva_resource_hints', 1 );

/**
 * Rentiva → Theme Settings → Colors overrides the locked design tokens only
 * when a site owner explicitly changes them — printed as a tiny inline
 * override so assets/css/variables.css never needs a build step to reflect
 * a color change.
 *
 * @return void
 */
function rentiva_print_color_overrides() {
	$defaults = array(
		'--rentiva-primary'      => '#1B5E3B',
		'--rentiva-primary-dark' => '#154D2E',
		'--rentiva-background'   => '#F9F8F5',
	);
	$overrides = array(
		'--rentiva-primary'      => rentiva_get_setting( 'color_primary', $defaults['--rentiva-primary'] ),
		'--rentiva-primary-dark' => rentiva_get_setting( 'color_primary_dark', $defaults['--rentiva-primary-dark'] ),
		'--rentiva-background'   => rentiva_get_setting( 'color_background', $defaults['--rentiva-background'] ),
	);

	$changed = array_diff_assoc( $overrides, $defaults );

	if ( empty( $changed ) ) {
		return;
	}

	echo '<style id="rentiva-color-overrides">:root{';
	foreach ( $changed as $property => $value ) {
		printf( '%s:%s;', esc_attr( $property ), esc_attr( $value ) );
	}
	echo '}</style>' . "\n";
}
add_action( 'wp_head', 'rentiva_print_color_overrides', 5 );

/**
 * Register every theme stylesheet/script up front (cheap — no I/O beyond the
 * function definitions below), then enqueue only what the current template needs.
 *
 * @return void
 */
function rentiva_register_assets() {
	$css_dir = RENTIVA_URI . 'assets/css/';
	$js_dir  = RENTIVA_URI . 'assets/js/';
	$ver     = RENTIVA_VERSION;

	// Design-system core — needed on every page, deliberately small & split so
	// a reviewer (or a child theme) can see exactly what governs what.
	$core_styles = array(
		'rentiva-variables'  => 'variables.css',
		'rentiva-base'       => 'base.css',
		'rentiva-typography' => 'typography.css',
		'rentiva-layout'     => 'layout.css',
		'rentiva-components' => 'components.css',
	);
	$deps = array();
	foreach ( $core_styles as $handle => $file ) {
		wp_register_style( $handle, $css_dir . $file, $deps, $ver );
		$deps[] = $handle;
	}
	$core_deps = $deps; // Every core handle in load order — reused as the dependency chain below.

	wp_register_style( 'rentiva-header', $css_dir . 'header.css', $core_deps, $ver );
	wp_register_style( 'rentiva-footer', $css_dir . 'footer.css', $core_deps, $ver );
	wp_register_style( 'rentiva-hero', $css_dir . 'hero.css', $core_deps, $ver );
	wp_register_style( 'rentiva-categories', $css_dir . 'categories.css', $core_deps, $ver );
	wp_register_style( 'rentiva-popular-rentals', $css_dir . 'popular-rentals.css', $core_deps, $ver );
	wp_register_style( 'rentiva-promo-banner', $css_dir . 'promo-banner.css', $core_deps, $ver );
	wp_register_style( 'rentiva-how-it-works', $css_dir . 'how-it-works.css', $core_deps, $ver );
	wp_register_style( 'rentiva-why-rentiva', $css_dir . 'why-rentiva.css', $core_deps, $ver );
	wp_register_style( 'rentiva-testimonial', $css_dir . 'testimonial.css', $core_deps, $ver );
	wp_register_style( 'rentiva-final-cta', $css_dir . 'final-cta.css', $core_deps, $ver );
	wp_register_style(
		'rentiva-home',
		$css_dir . 'home.css',
		array_merge(
			$core_deps,
			array( 'rentiva-hero', 'rentiva-categories', 'rentiva-popular-rentals', 'rentiva-promo-banner', 'rentiva-how-it-works', 'rentiva-why-rentiva', 'rentiva-testimonial', 'rentiva-final-cta' )
		),
		$ver
	);
	wp_register_style( 'rentiva-archive', $css_dir . 'archive.css', $core_deps, $ver );
	wp_register_style( 'rentiva-single-item', $css_dir . 'single-item.css', $core_deps, $ver );
	wp_register_style( 'rentiva-booking', $css_dir . 'booking.css', $core_deps, $ver );
	wp_register_style( 'rentiva-plugin-item-details', $css_dir . 'plugin-item-details.css', $core_deps, $ver );
	wp_register_style( 'rentiva-content', $css_dir . 'content.css', $core_deps, $ver );
	wp_register_style( 'rentiva-admin', $css_dir . 'admin.css', array(), $ver );
	wp_register_style(
		'rentiva-responsive',
		$css_dir . 'responsive.css',
		array_merge( $core_deps, array( 'rentiva-header', 'rentiva-footer', 'rentiva-home', 'rentiva-archive', 'rentiva-single-item' ) ),
		$ver
	);

	// Scripts — vanilla JS, in_footer, no framework.
	wp_register_script( 'rentiva-header-scroll', $js_dir . 'header-scroll.js', array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_register_script( 'rentiva-mobile-nav', $js_dir . 'mobile-nav.js', array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_register_script( 'rentiva-favorites', $js_dir . 'favorites.js', array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_register_script( 'rentiva-search-suggest', $js_dir . 'search-suggest.js', array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_register_script( 'rentiva-gallery-thumbnails', $js_dir . 'gallery-thumbnails.js', array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_register_script( 'rentiva-sticky-card', $js_dir . 'sticky-card.js', array(), $ver, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_register_script( 'rentiva-admin-setup', $js_dir . 'admin-setup.js', array( 'jquery' ), $ver, array( 'in_footer' => true ) );
}
add_action( 'init', 'rentiva_register_assets' );

/**
 * Decide what the current request actually needs and enqueue it.
 *
 * @return void
 */
function rentiva_enqueue_assets() {
	rentiva_enqueue_fonts();

	wp_enqueue_style( 'rentiva-variables' );
	wp_enqueue_style( 'rentiva-base' );
	wp_enqueue_style( 'rentiva-typography' );
	wp_enqueue_style( 'rentiva-layout' );
	wp_enqueue_style( 'rentiva-components' );
	wp_enqueue_style( 'rentiva-header' );
	wp_enqueue_style( 'rentiva-footer' );
	wp_enqueue_script( 'rentiva-header-scroll' );
	wp_enqueue_script( 'rentiva-mobile-nav' );

	$is_item_archive_context = rentiva_has_booking_plugin() && ( is_post_type_archive( 'rbfw_item' ) || is_tax( array( 'rbfw_item_caregory', 'rbfw_item_location' ) ) );
	$is_single_item          = rentiva_has_booking_plugin() && is_singular( 'rbfw_item' );

	if ( is_front_page() && ! is_paged() ) {
		wp_enqueue_style( 'rentiva-home' );
		wp_enqueue_script( 'rentiva-favorites' );
		wp_enqueue_script( 'rentiva-search-suggest' );
		wp_localize_script(
			'rentiva-search-suggest',
			'rentivaSearch',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'rentiva_search_suggest' ),
				'minChars' => 2,
			)
		);
	}

	if ( $is_item_archive_context ) {
		wp_enqueue_style( 'rentiva-archive' );
		wp_enqueue_script( 'rentiva-favorites' );
	}

	if ( $is_single_item && rentiva_use_theme_single_item_layout() ) {
		wp_enqueue_style( 'rentiva-single-item' );
		wp_enqueue_style( 'rentiva-booking' );
		wp_enqueue_script( 'rentiva-favorites' );
		wp_enqueue_script( 'rentiva-gallery-thumbnails' );
		wp_enqueue_script( 'rentiva-sticky-card' );
	}

	if ( $is_single_item && ! rentiva_use_theme_single_item_layout() ) {
		// Plugin details mode: keep the plugin's own design, only load layout
		// safety + width alignment so its markup doesn't collide with our container.
		wp_enqueue_style( 'rentiva-plugin-item-details' );
	}

	if ( is_singular( 'post' ) || is_page() || is_home() || ( is_archive() && ! $is_item_archive_context ) || is_search() || is_404() ) {
		wp_enqueue_style( 'rentiva-content' );
	}

	if ( rentiva_has_woocommerce() && ( is_cart() || is_checkout() || is_account_page() || is_shop() || is_product() ) ) {
		wp_enqueue_style( 'rentiva-booking' );
	}

	wp_enqueue_style( 'rentiva-responsive' );

	// Comment reply script only where it's actually usable (reviews on rbfw_item, or ordinary post comments).
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'rentiva_enqueue_assets' );

/**
 * Editor styles so the block editor roughly matches the front end typography
 * and color palette.
 *
 * @return void
 */
function rentiva_editor_assets() {
	add_theme_support( 'editor-styles' );
	add_editor_style(
		array(
			'assets/css/variables.css',
			'assets/css/base.css',
			'assets/css/typography.css',
		)
	);
}
add_action( 'after_setup_theme', 'rentiva_editor_assets' );
