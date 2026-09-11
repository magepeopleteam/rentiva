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

/**
 * Native WordPress search (`?s=…`) always means "find a rental" on this
 * site — there's no header search box wired to anything else, and
 * WordPress's own search would otherwise mix in RBFW's auto-generated
 * WooCommerce product record for every item (plumbing for cart/checkout,
 * never meant to be browsed directly) alongside plain pages, rendered with
 * generic post-list markup instead of the rental grid.
 *
 * Every in-theme search field (the hero's, the archive sidebar's) already
 * posts to the rentals archive as `item_search` — see the matching note on
 * each of those fields for why it's never named `s` — so this redirect only
 * ever fires for a request that reached `?s=` some other way (an old
 * bookmark, a link from outside the theme, WordPress's own default
 * search.php/searchform.php). It sends that request to the same query on
 * archive-rbfw_item.php, once, so "search" only ever has one design on this
 * site. 302, not 301: this is routing based on the current request, not a
 * permanent move of a fixed URL.
 *
 * @return void
 */
function rentiva_redirect_native_search_to_rentals() {
	if ( ! is_search() || ! rentiva_has_booking_plugin() ) {
		return;
	}

	$rentals_url  = rentiva_get_rentals_page_url();
	$redirect_url = add_query_arg( 'item_search', rawurlencode( get_search_query() ), $rentals_url );

	wp_safe_redirect( esc_url_raw( $redirect_url ), 302 );
	exit;
}
add_action( 'template_redirect', 'rentiva_redirect_native_search_to_rentals' );
