<?php
/**
 * Documented action hooks + the theme's own AJAX endpoints.
 *
 * Every extension point a child theme/plugin can use is listed in
 * docs/hooks.md — keep that file in sync with what's actually fired here.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX: header/hero search suggestions.
 *
 * Reads the query the same way for logged-in and anonymous visitors — this
 * is a read-only autocomplete, not a state-changing action, so both hooks
 * point at the same callback.
 *
 * @return void
 */
function rentiva_ajax_search_suggest() {
	check_ajax_referer( 'rentiva_search_suggest', 'nonce' );

	$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';

	if ( '' === $term || ! rentiva_has_booking_plugin() || ! class_exists( 'Rentiva_Rental_Adapter' ) ) {
		wp_send_json_success( array() );
	}

	$results = Rentiva_Rental_Adapter::suggest_items( $term, 8 );

	wp_send_json_success( $results );
}
add_action( 'wp_ajax_rentiva_search_suggest', 'rentiva_ajax_search_suggest' );
add_action( 'wp_ajax_nopriv_rentiva_search_suggest', 'rentiva_ajax_search_suggest' );
