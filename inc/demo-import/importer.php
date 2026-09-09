<?php
/**
 * One-click demo content importer. Creates the categories, locations, and
 * rental items defined in sample-data.php as real `rbfw_item` posts —
 * never fabricates plugin-internal state it doesn't understand; every
 * meta key written here is one already verified in
 * inc/integrations/booking-plugin.php / docs/booking-integration.md.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the "Import demo content" button (see inc/admin/setup-wizard.php).
 *
 * @return void
 */
function rentiva_handle_import_demo() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'rentiva' ) );
	}
	check_admin_referer( 'rentiva_import_demo' );

	if ( ! rentiva_has_booking_plugin() ) {
		wp_safe_redirect( add_query_arg( 'rentiva_demo_imported', 'missing-plugin', admin_url( 'admin.php?page=rentiva-settings' ) ) );
		exit;
	}

	rentiva_import_demo_content();

	wp_safe_redirect( add_query_arg( 'rentiva_demo_imported', '1', admin_url( 'admin.php?page=rentiva-settings' ) ) );
	exit;
}
add_action( 'admin_post_rentiva_import_demo', 'rentiva_handle_import_demo' );

/**
 * Show a success/notice banner on the settings screen after import.
 *
 * @return void
 */
function rentiva_demo_import_notice() {
	if ( ! isset( $_GET['rentiva_demo_imported'] ) || ! isset( $_GET['page'] ) || 'rentiva-settings' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display-only redirect flag, no state change happens here.
		return;
	}

	if ( 'missing-plugin' === $_GET['rentiva_demo_imported'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Install and activate Booking and Rental Manager for WooCommerce before importing demo content.', 'rentiva' ) . '</p></div>';
		return;
	}

	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Demo content imported.', 'rentiva' ) . '</p></div>';
}
add_action( 'admin_notices', 'rentiva_demo_import_notice' );

/**
 * Create the demo categories/locations/items, skipping anything that
 * already exists by title/slug so the button is safe to click more than once.
 *
 * @return void
 */
function rentiva_import_demo_content() {
	$category_ids = array();
	foreach ( rentiva_demo_categories() as $category_name ) {
		$category_ids[ $category_name ] = rentiva_get_or_create_term( $category_name, 'rbfw_item_caregory' );
	}

	$location_ids = array();
	foreach ( rentiva_demo_locations() as $location_name ) {
		$location_ids[ $location_name ] = rentiva_get_or_create_term( $location_name, 'rbfw_item_location' );
	}

	foreach ( rentiva_demo_items() as $item ) {
		$existing = new WP_Query(
			array(
				'post_type'              => 'rbfw_item',
				'post_status'            => 'any',
				'title'                  => $item['title'],
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);
		if ( $existing->have_posts() ) {
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'rbfw_item',
				'post_title'   => $item['title'],
				'post_excerpt' => $item['excerpt'],
				'post_content' => $item['content'],
				'post_status'  => 'publish',
			)
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		if ( ! empty( $category_ids[ $item['category'] ] ) ) {
			wp_set_object_terms( $post_id, (int) $category_ids[ $item['category'] ], 'rbfw_item_caregory' );
		}
		if ( ! empty( $location_ids[ $item['location'] ] ) ) {
			wp_set_object_terms( $post_id, (int) $location_ids[ $item['location'] ], 'rbfw_item_location' );
		}

		update_post_meta( $post_id, 'rbfw_item_type', 'bike_car_md' );
		update_post_meta( $post_id, 'rbfw_enable_daily_rate', 'yes' );
		update_post_meta( $post_id, 'rbfw_daily_rate', (float) $item['daily_rate'] );
		update_post_meta( $post_id, 'rbfw_enable_hourly_rate', 'no' );

		if ( ! empty( $item['weekly_rate'] ) ) {
			update_post_meta( $post_id, 'rbfw_enable_weekly_rate', 'yes' );
			update_post_meta( $post_id, 'rbfw_weekly_rate', (float) $item['weekly_rate'] );
		}

		$feature_categories = array();
		if ( ! empty( $item['specs'] ) ) {
			$feature_categories[] = array(
				'cat_title'    => __( 'Specifications', 'rentiva' ),
				'cat_features' => rentiva_demo_features_from_titles( $item['specs'] ),
			);
		}
		if ( ! empty( $item['included'] ) ) {
			$feature_categories[] = array(
				'cat_title'    => __( "What's Included", 'rentiva' ),
				'cat_features' => rentiva_demo_features_from_titles( $item['included'] ),
			);
		}
		if ( ! empty( $feature_categories ) ) {
			update_post_meta( $post_id, 'rbfw_feature_category', $feature_categories );
		}
	}
}

/**
 * @param string[] $titles
 * @return array<int,array{icon:string,title:string}>
 */
function rentiva_demo_features_from_titles( $titles ) {
	$features = array();
	foreach ( $titles as $title ) {
		$features[] = array(
			'icon'  => 'fas fa-check-circle',
			'title' => $title,
		);
	}
	return $features;
}

/**
 * Find a term by name, creating it if it doesn't exist yet.
 *
 * @param string $name
 * @param string $taxonomy
 * @return int Term ID, or 0 on failure.
 */
function rentiva_get_or_create_term( $name, $taxonomy ) {
	$term = get_term_by( 'name', $name, $taxonomy );
	if ( $term ) {
		return (int) $term->term_id;
	}

	$result = wp_insert_term( $name, $taxonomy );
	if ( is_wp_error( $result ) ) {
		return 0;
	}

	return (int) $result['term_id'];
}
