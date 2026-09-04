<?php
/**
 * Small, dependency-free helper functions used across the theme.
 *
 * Nothing in this file talks to a database table the theme doesn't own, and
 * nothing here makes pricing/availability decisions — it only formats/escapes.
 * See inc/integrations/booking-plugin.php for the adapter that actually reads
 * "Booking and Rental Manager for WooCommerce" (RBFW) plugin data.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the rental-booking plugin this theme integrates with is active.
 *
 * Centralised so every template checks the same thing the same way.
 *
 * @return bool
 */
function rentiva_has_booking_plugin() {
	return post_type_exists( 'rbfw_item' ) && class_exists( 'RBFW_Function' );
}

/**
 * Whether WooCommerce is active.
 *
 * @return bool
 */
function rentiva_has_woocommerce() {
	return class_exists( 'WooCommerce' );
}

/**
 * Whether Elementor is active.
 *
 * @return bool
 */
function rentiva_has_elementor() {
	return did_action( 'elementor/loaded' );
}

/**
 * URL of the "browse all rentals" page.
 *
 * `rbfw_item` is registered with a real archive (unlike some booking CPTs),
 * so `get_post_type_archive_link()` is the normal path — but the archive
 * slug itself is admin-configurable inside the plugin's own Quick Setup
 * (`rbfw_basic_gen_settings['rbfw_rent_slug']`, default `rent`), and the
 * plugin recomputes it defensively on every `init`. Never hardcode `/rent/`
 * anywhere in the theme — always resolve the URL through this helper.
 *
 * @return string
 */
function rentiva_get_rentals_page_url() {
	if ( ! rentiva_has_booking_plugin() ) {
		return home_url( '/' );
	}

	$archive_link = get_post_type_archive_link( 'rbfw_item' );

	return $archive_link ? $archive_link : home_url( '/' );
}

/**
 * The RBFW plugin's currently configured archive slug, read live (never
 * cached across requests — the plugin itself avoids caching this for the
 * same reason, see admin/custom_post.php).
 *
 * @return string
 */
function rentiva_get_rentals_slug() {
	$settings = get_option( 'rbfw_basic_gen_settings', array() );
	if ( is_array( $settings ) && ! empty( $settings['rbfw_rent_slug'] ) ) {
		return sanitize_title( $settings['rbfw_rent_slug'] );
	}
	return 'rent';
}

/**
 * Fetch a single Rentiva theme setting with a safe fallback.
 *
 * All Rentiva → Theme Settings values live in one option (`rentiva_settings`)
 * so activating/deactivating the theme never scatters dozens of orphaned
 * wp_options rows. See inc/admin/theme-settings.php for the settings screen.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Fallback if unset.
 * @return mixed
 */
function rentiva_get_setting( $key, $default = '' ) {
	static $settings = null;

	// The Theme Settings screen's live-preview iframe overlays unsaved,
	// sanitized values here for the span of a single preview request —
	// never persisted, never touches the DB option.
	if ( isset( $GLOBALS['rentiva_preview_overrides'] ) && array_key_exists( $key, $GLOBALS['rentiva_preview_overrides'] ) ) {
		$value = $GLOBALS['rentiva_preview_overrides'][ $key ];
		return ( '' === $value || null === $value ) ? $default : $value;
	}

	if ( null === $settings ) {
		$settings = get_option( 'rentiva_settings', array() );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}
	}

	if ( ! array_key_exists( $key, $settings ) ) {
		return $default;
	}

	// The Theme Settings form always writes every registered field on save,
	// including ones left blank, as ''. Without this, saving the settings
	// screen even once with an image field left empty would permanently
	// blank the homepage hero/copy instead of falling back to the theme's
	// bundled defaults.
	$value = $settings[ $key ];
	return ( '' === $value || null === $value ) ? $default : $value;
}

/**
 * Whether single `rbfw_item` pages should render Rentiva's own themed page
 * (default) or defer entirely to the plugin's bundled design.
 *
 * Unlike a `single_template` filter fight, RBFW resolves its own templates
 * through `RBFW_Function::get_template_path()`, which already checks
 * `get_stylesheet_directory() . '/templates/...'` first — so Rentiva's
 * `templates/single/single-rbfw.php` wins automatically by file presence.
 * This setting only controls what THAT theme file does: render Rentiva's
 * own layout, or `include` the plugin's bundled template directly.
 *
 * @return bool True to use Rentiva's themed layout (default), false to defer to the plugin.
 */
function rentiva_use_theme_single_item_layout() {
	return 'plugin' !== rentiva_get_setting( 'single_item_layout', 'theme' );
}

/**
 * Render an inline SVG icon from assets/icons/ with output escaped via wp_kses.
 *
 * Icons are trusted theme assets (not user input), but we still run them
 * through a strict SVG allow-list rather than echoing raw file contents,
 * so a compromised/edited icon file can't inject arbitrary script.
 *
 * @param string $name  Icon file name without extension, e.g. "search".
 * @param array  $attrs Optional extra attributes merged onto the root <svg>, e.g. ['class' => 'icon-lg'].
 * @return void
 */
function rentiva_icon( $name, $attrs = array() ) {
	echo rentiva_get_icon( $name, $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside rentiva_get_icon() via wp_kses.
}

/**
 * Same as rentiva_icon() but returns the markup instead of echoing it.
 *
 * @param string $name  Icon file name without extension.
 * @param array  $attrs Optional extra attributes merged onto the root <svg>.
 * @return string Escaped SVG markup, or an empty string if the icon doesn't exist.
 */
function rentiva_get_icon( $name, $attrs = array() ) {
	$name = sanitize_file_name( $name );
	$path = RENTIVA_DIR . 'assets/icons/' . $name . '.svg';

	if ( ! is_readable( $path ) ) {
		return '';
	}

	$svg = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local theme asset, not a remote/user-supplied path.
	if ( false === $svg ) {
		return '';
	}

	if ( ! empty( $attrs ) ) {
		$attr_string = '';
		foreach ( $attrs as $attr_key => $attr_value ) {
			$attr_string .= ' ' . esc_attr( $attr_key ) . '="' . esc_attr( $attr_value ) . '"';
		}
		$svg = preg_replace( '/<svg/', '<svg' . $attr_string, $svg, 1 );
	}

	$allowed_svg = array(
		'svg'      => array(
			'class'           => true,
			'width'           => true,
			'height'          => true,
			'viewbox'         => true,
			'viewBox'         => true,
			'fill'            => true,
			'fill-opacity'    => true,
			'fillopacity'     => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'aria-hidden'     => true,
			'focusable'       => true,
			'role'            => true,
		),
		'path'     => array(
			'd'               => true,
			'fill'            => true,
			'fill-opacity'    => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
		),
		'circle'   => array(
			'cx'   => true,
			'cy'   => true,
			'r'    => true,
			'fill' => true,
		),
		'line'     => array(
			'x1' => true,
			'y1' => true,
			'x2' => true,
			'y2' => true,
		),
		'rect'     => array(
			'x'      => true,
			'y'      => true,
			'width'  => true,
			'height' => true,
			'rx'     => true,
			'fill'   => true,
		),
		'polyline' => array(
			'points' => true,
		),
		'g'        => array(
			'fill' => true,
		),
	);

	return wp_kses( $svg, $allowed_svg );
}

/**
 * Format a price for display using the site's WooCommerce currency settings
 * when available, otherwise a plain "$X" fallback. Never invents a price —
 * callers always pass a real numeric value sourced from Rentiva_Rental_Adapter.
 *
 * @param float|int|string $amount
 * @return string Escaped HTML.
 */
function rentiva_format_price( $amount ) {
	if ( '' === $amount || null === $amount ) {
		return '';
	}

	if ( rentiva_has_woocommerce() && function_exists( 'wc_price' ) ) {
		return wc_price( $amount ); // wc_price() already escapes.
	}

	return esc_html( '$' . number_format_i18n( (float) $amount, 2 ) );
}

/**
 * Truncate text to a word count without cutting mid-word, appending an ellipsis.
 *
 * @param string $text
 * @param int    $words
 * @return string Plain text, not yet escaped by design (callers decide context: esc_html vs wp_kses_post).
 */
function rentiva_trim_words( $text, $words = 20 ) {
	return wp_trim_words( wp_strip_all_tags( $text ), $words, '…' );
}

/**
 * Whether the homepage should be handed over to an admin-built page (Elementor)
 * instead of the theme's built-in front-page.php section layout.
 *
 * Deliberately conservative: a fresh/default install always falls through to
 * the built-in homepage — nothing changes until an admin explicitly builds a
 * static front page with real content via Rentiva Setup / Elementor.
 *
 * @return bool
 */
function rentiva_homepage_uses_custom_builder() {
	if ( 'page' !== get_option( 'show_on_front' ) ) {
		return false;
	}

	$front_id = (int) get_option( 'page_on_front' );
	if ( ! $front_id ) {
		return false;
	}

	$front_page = get_post( $front_id );
	if ( ! $front_page || 'publish' !== $front_page->post_status ) {
		return false;
	}

	$has_content   = '' !== trim( (string) $front_page->post_content );
	$has_elementor = 'builder' === get_post_meta( $front_id, '_elementor_edit_mode', true );

	return $has_content || $has_elementor;
}

/**
 * Safe wrapper around get_template_part() that lets template-parts receive
 * an associative array of local variables without polluting global scope.
 *
 * @param string $slug Template part slug, e.g. 'cards/rental-card'.
 * @param string $name Optional template part name.
 * @param array  $args Variables made available to the part as $args.
 * @return void
 */
function rentiva_template_part( $slug, $name = '', $args = array() ) {
	get_template_part( $slug, $name, $args );
}
