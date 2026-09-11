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
	foreach ( rentiva_demo_categories() as $category_name => $category_photo ) {
		$term_id = rentiva_get_or_create_term( $category_name, 'rbfw_item_caregory' );
		$category_ids[ $category_name ] = $term_id;

		if ( $term_id && ! get_term_meta( $term_id, 'rentiva_category_image_id', true ) ) {
			$image_id = rentiva_sideload_demo_photo( $category_photo, $category_name . ' category' );
			if ( $image_id ) {
				update_term_meta( $term_id, 'rentiva_category_image_id', $image_id );
			}
		}
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

		$is_new  = ! $existing->have_posts();
		$post_id = $is_new ? 0 : (int) $existing->posts[0];

		if ( $is_new ) {
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
				// The plugin's own front-end listing/search (rbfw_shortcodes.php,
				// used by the category archive template and the homepage) filters
				// by this postmeta, NOT by the rbfw_item_caregory taxonomy
				// relationship set above — the two are separate, undocumented
				// storage for "category" on this plugin. Without this, an item
				// shows correctly in wp-admin (real term assignment, real count)
				// but never appears on that category's own front-end page.
				// Format required by rbfw_build_category_meta_clause(): a
				// serialized array of category name strings — update_post_meta()
				// serializes the array automatically.
				update_post_meta( $post_id, 'rbfw_categories', array( $item['category'] ) );
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

		// Backfilled for both a brand-new item and one that already existed
		// (e.g. created by an earlier version of this importer) — without
		// this, the plugin's own stock logic treats a never-set quantity as
		// zero, so the item shows "out of stock" regardless of when it was
		// created. '10' matches the plugin's own bundled demo importer
		// (inc/rbfw_import_demo.php) exactly.
		if ( '' === get_post_meta( $post_id, 'rbfw_item_stock_quantity', true ) ) {
			update_post_meta( $post_id, 'rbfw_item_stock_quantity', '10' );
		}

		if ( ! empty( $item['photo'] ) && ! get_post_thumbnail_id( $post_id ) ) {
			$image_id = rentiva_sideload_demo_photo( $item['photo'], $item['title'] );
			if ( $image_id ) {
				set_post_thumbnail( $post_id, $image_id );
			}
		}

		// Backfilled the same way as stock quantity above — only when the
		// item has no FAQ entries of its own yet, so an admin who's already
		// written real FAQs (or deliberately cleared the demo ones) never
		// has them silently replaced by re-running this importer. Renders
		// via the plugin's own FAQ accordion (admin/settings/Faq.php,
		// `mep_event_faq` post meta) — nothing in the theme renders these.
		$existing_faqs = get_post_meta( $post_id, 'mep_event_faq', true );
		if ( empty( $existing_faqs ) ) {
			update_post_meta( $post_id, 'mep_event_faq', rentiva_demo_faqs( $item['title'] ) );
			update_post_meta( $post_id, 'rbfw_enable_faq_content', 'yes' );
		}
	}

	rentiva_import_demo_menus();
	rentiva_import_demo_homepage_images();

	update_option( 'rentiva_demo_imported_at', current_time( 'mysql' ) );
}

/**
 * Sideload the Hero/Promo Banner/Why Rentiva/Testimonial photos
 * (rentiva_demo_homepage_images(), sample-data.php) into their matching
 * `rentiva_settings` key — but only a key that's still genuinely unset, so
 * a site that's already picked its own image (via Theme Settings before,
 * or directly in the Elementor widget now) is never overwritten. Each
 * fetched at that field's own registered crop size (inc/setup.php) so it
 * needs no further cropping.
 *
 * @return void
 */
function rentiva_import_demo_homepage_images() {
	$settings = get_option( 'rentiva_settings', array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	$changed = false;
	foreach ( rentiva_demo_homepage_images() as $key => $filename ) {
		if ( ! empty( $settings[ $key ] ) ) {
			continue;
		}

		$image_id = rentiva_sideload_demo_photo( $filename, $key );
		if ( $image_id ) {
			$settings[ $key ] = $image_id;
			$changed = true;
		}
	}

	if ( $changed ) {
		update_option( 'rentiva_settings', $settings );
	}
}

/**
 * Attaches one bundled demo photo (assets/images/demo/$filename — see
 * rentiva_demo_categories() in sample-data.php for why these are permanent
 * theme files rather than fetched live) into the media library. These used
 * to be sideloaded from Unsplash at import time; downloaded once and
 * committed as real files instead, so import never depends on outbound
 * internet access and the homepage/catalog are ready immediately on theme
 * activation (see rentiva_maybe_auto_provision_demo() below). Every caller
 * still treats a `0` return as "skip this image, keep going" — a missing or
 * unreadable bundled file shouldn't abort the rest of the import.
 *
 * @param string $filename    Filename under assets/images/demo/.
 * @param string $description Used only to build a descriptive attachment title.
 * @return int Attachment id, or 0 on failure.
 */
function rentiva_sideload_demo_photo( $filename, $description = '' ) {
	if ( ! function_exists( 'media_handle_sideload' ) ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$source = RENTIVA_DIR . 'assets/images/demo/' . $filename;
	if ( ! is_readable( $source ) ) {
		return 0;
	}

	$tmp_file = wp_tempnam( $filename );
	if ( ! copy( $source, $tmp_file ) ) {
		return 0;
	}

	$file_array = array(
		'name'     => $filename,
		'tmp_name' => $tmp_file,
	);

	$attachment_id = media_handle_sideload( $file_array, 0, $description );

	if ( is_wp_error( $attachment_id ) ) {
		if ( file_exists( $tmp_file ) ) {
			wp_delete_file( $tmp_file );
		}
		return 0;
	}

	return (int) $attachment_id;
}

/**
 * Create the 4 real, editable nav menus (Primary Navigation, Footer —
 * Explore/Company/Support) from the theme's own default nav items
 * (rentiva_default_primary_nav_items() / rentiva_default_footer_nav_items()
 * in inc/template-functions.php — the exact same items the header/footer
 * already show when no menu is assigned), and assign each to its theme
 * location. Without this, Appearance → Menus shows nothing at all even
 * though the site looks fully populated — there's nothing there to click
 * and see how to change, since that content was only ever PHP fallback
 * markup, never real menu items. Called after categories/items above so
 * "Footer — Explore" picks up the real imported categories.
 *
 * Safe to run more than once: an existing menu with the same name is
 * reused (never duplicated), items already present (matched by title)
 * are skipped, and a location that already has ANY menu assigned — even
 * one the admin picked themselves — is left alone rather than reassigned.
 *
 * @return void
 */
function rentiva_import_demo_menus() {
	$menus = array(
		'primary'        => array(
			'name'  => __( 'Primary Navigation', 'rentiva' ),
			'items' => rentiva_default_primary_nav_items(),
		),
		'footer-explore' => array(
			'name'  => __( 'Footer — Explore', 'rentiva' ),
			'items' => rentiva_default_footer_nav_items( 'footer-explore' ),
		),
		'footer-company' => array(
			'name'  => __( 'Footer — Company', 'rentiva' ),
			'items' => rentiva_default_footer_nav_items( 'footer-company' ),
		),
		'footer-support' => array(
			'name'  => __( 'Footer — Support', 'rentiva' ),
			'items' => rentiva_default_footer_nav_items( 'footer-support' ),
		),
	);

	$locations = get_nav_menu_locations();

	foreach ( $menus as $location => $menu_data ) {
		$menu    = wp_get_nav_menu_object( $menu_data['name'] );
		$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( $menu_data['name'] );

		if ( is_wp_error( $menu_id ) || ! $menu_id ) {
			continue;
		}

		$existing_items  = wp_get_nav_menu_items( $menu_id );
		$existing_titles = $existing_items ? wp_list_pluck( $existing_items, 'title' ) : array();

		$position = 1;
		foreach ( $menu_data['items'] as $item ) {
			if ( ! in_array( $item['label'], $existing_titles, true ) ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'    => $item['label'],
						'menu-item-url'      => $item['url'],
						'menu-item-status'   => 'publish',
						'menu-item-position' => $position,
					)
				);
			}
			++$position;
		}

		if ( empty( $locations[ $location ] ) ) {
			$locations[ $location ] = $menu_id;
		}
	}

	set_theme_mod( 'nav_menu_locations', $locations );
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
