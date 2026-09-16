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

	if ( ! rentiva_demo_import_ready() ) {
		wp_safe_redirect( add_query_arg( 'rentiva_demo_imported', 'missing-plugin', rentiva_get_setup_step_url( 'plugins' ) ) );
		exit;
	}

	if ( is_wp_error( rentiva_import_demo_content() ) ) {
		// Step 2 lists exactly what's still missing (rentiva_get_demo_import_problems()).
		wp_safe_redirect( add_query_arg( 'rentiva_demo_imported', 'incomplete', rentiva_get_setup_step_url( 'demo' ) ) );
		exit;
	}

	// Back onto the wizard's Import step, so Continue picks up where the admin left off.
	wp_safe_redirect( add_query_arg( 'rentiva_demo_imported', '1', rentiva_get_setup_step_url( 'demo' ) ) );
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

	if ( 'incomplete' === $_GET['rentiva_demo_imported'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Demo import finished, but some content is still missing — see the list below, then run the import again.', 'rentiva' ) . '</p></div>';
		return;
	}

	echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Demo content imported.', 'rentiva' ) . '</p></div>';
}
add_action( 'admin_notices', 'rentiva_demo_import_notice' );

/**
 * Create the demo categories/locations/items, nav menus and homepage images
 * in one go — reusing anything that already exists and repairing whatever an
 * earlier import left incomplete, so it's safe to run more than once. Used by
 * activation auto-provisioning and the no-JS "Import Demo Content" fallback;
 * Setup's Step 2 runs the exact same steps one AJAX request at a time instead
 * (rentiva_ajax_import_demo_step()), so it can show live progress without a
 * page reload.
 *
 * @return true|WP_Error WP_Error when the booking plugin isn't ready, or when
 *                       the final check still finds missing content.
 */
function rentiva_import_demo_content() {
	if ( ! rentiva_demo_import_ready() ) {
		return rentiva_demo_import_not_ready_error();
	}

	$result = true;
	foreach ( rentiva_get_demo_import_steps() as $step ) {
		$result = rentiva_run_demo_import_step( $step['id'] );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
	}

	return $result;
}

/**
 * @return WP_Error
 */
function rentiva_demo_import_not_ready_error() {
	return new WP_Error( 'rentiva_booking_plugin_not_ready', __( 'Install and activate Booking and Rental Manager for WooCommerce before importing demo content.', 'rentiva' ) );
}

/**
 * The demo import as an ordered list of small, independently runnable steps.
 * Each one sideloads at most one bundled photo, so no single request does
 * more than one image's worth of resizing — a slow host can't hit a PHP
 * execution-time limit mid-import. Every step is idempotent, so a failed
 * run resumes safely from the step that failed.
 *
 * `group` ties each step to one checklist row on Setup's Step 2
 * (rentiva_get_demo_import_groups()); `finish` has no row of its own.
 *
 * @return array<int,array{id:string,group:string}>
 */
function rentiva_get_demo_import_steps() {
	$steps = array();

	foreach ( array_keys( array_keys( rentiva_demo_categories() ) ) as $index ) {
		$steps[] = array(
			'id'    => 'category-' . $index,
			'group' => 'categories',
		);
	}

	$steps[] = array(
		'id'    => 'locations',
		'group' => 'locations',
	);

	foreach ( array_keys( rentiva_demo_items() ) as $index ) {
		$steps[] = array(
			'id'    => 'item-' . $index,
			'group' => 'items',
		);
	}

	$steps[] = array(
		'id'    => 'menus',
		'group' => 'menus',
	);

	foreach ( array_keys( array_keys( rentiva_demo_homepage_images() ) ) as $index ) {
		$steps[] = array(
			'id'    => 'homepage-image-' . $index,
			'group' => 'homepage',
		);
	}

	$steps[] = array(
		'id'    => 'finish',
		'group' => 'finish',
	);

	return $steps;
}

/**
 * Checklist rows shown while Setup's Step 2 imports — step group => label.
 *
 * @return array<string,string>
 */
function rentiva_get_demo_import_groups() {
	return array(
		'categories' => __( 'Rental categories', 'rentiva' ),
		'locations'  => __( 'Pickup locations', 'rentiva' ),
		'items'      => __( 'Rental items', 'rentiva' ),
		'menus'      => __( 'Navigation menus', 'rentiva' ),
		'homepage'   => __( 'Homepage images', 'rentiva' ),
	);
}

/**
 * Run one step from rentiva_get_demo_import_steps().
 *
 * @param string $step_id
 * @return bool|WP_Error False when the step id doesn't exist; WP_Error when
 *                       the `finish` check finds content still missing.
 */
function rentiva_run_demo_import_step( $step_id ) {
	if ( preg_match( '/^(category|item|homepage-image)-(\d+)$/', $step_id, $matches ) ) {
		$index = (int) $matches[2];

		if ( 'category' === $matches[1] ) {
			$categories = rentiva_demo_categories();
			$names      = array_keys( $categories );
			if ( ! isset( $names[ $index ] ) ) {
				return false;
			}
			rentiva_import_demo_category( $names[ $index ], $categories[ $names[ $index ] ] );
			return true;
		}

		if ( 'item' === $matches[1] ) {
			$items = rentiva_demo_items();
			if ( ! isset( $items[ $index ] ) ) {
				return false;
			}
			rentiva_import_demo_item( $items[ $index ] );
			return true;
		}

		$images = rentiva_demo_homepage_images();
		$keys   = array_keys( $images );
		if ( ! isset( $keys[ $index ] ) ) {
			return false;
		}
		rentiva_import_demo_homepage_image( $keys[ $index ], $images[ $keys[ $index ] ] );
		return true;
	}

	switch ( $step_id ) {
		case 'locations':
			foreach ( rentiva_demo_locations() as $location_name ) {
				rentiva_get_or_create_term( $location_name, 'rbfw_item_location' );
			}
			return true;

		case 'menus':
			rentiva_import_demo_menus();
			return true;

		case 'finish':
			$problems = rentiva_get_demo_import_problems();
			if ( $problems ) {
				$shown = array_slice( $problems, 0, 5 );
				if ( count( $problems ) > 5 ) {
					/* translators: %d: number of further problems not listed. */
					$shown[] = sprintf( _n( '…and %d more.', '…and %d more.', count( $problems ) - 5, 'rentiva' ), count( $problems ) - 5 );
				}
				return new WP_Error( 'rentiva_demo_incomplete', __( 'Some demo content still couldn\'t be created:', 'rentiva' ) . ' ' . implode( ' ', $shown ) );
			}
			update_option( 'rentiva_demo_imported_at', current_time( 'mysql' ) );
			return true;
	}

	return false;
}

/**
 * AJAX: run one demo import step — Setup's Step 2 "Import Demo Content"
 * button (assets/js/admin-setup.js) calls this once per step, in order.
 *
 * @return void
 */
function rentiva_ajax_import_demo_step() {
	check_ajax_referer( 'rentiva_import_demo' );

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'rentiva' ) ), 403 );
	}

	if ( ! rentiva_demo_import_ready() ) {
		wp_send_json_error( array( 'message' => rentiva_demo_import_not_ready_error()->get_error_message() ) );
	}

	$step_id  = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';
	$step_ids = wp_list_pluck( rentiva_get_demo_import_steps(), 'id' );
	$result   = in_array( $step_id, $step_ids, true ) ? rentiva_run_demo_import_step( $step_id ) : false;

	if ( is_wp_error( $result ) ) {
		wp_send_json_error(
			array(
				'code'    => $result->get_error_code(),
				'message' => $result->get_error_message(),
			)
		);
	}

	if ( ! $result ) {
		wp_send_json_error( array( 'message' => __( 'Unknown import step.', 'rentiva' ) ) );
	}

	$response = array( 'step' => $step_id );

	if ( 'finish' === $step_id ) {
		$response['importedMessage'] = sprintf(
			/* translators: %s: date/time of last import */
			__( 'Imported on %s. Running again will not duplicate existing demo items.', 'rentiva' ),
			mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), get_option( 'rentiva_demo_imported_at' ) )
		);
	}

	wp_send_json_success( $response );
}
add_action( 'wp_ajax_rentiva_import_demo_step', 'rentiva_ajax_import_demo_step' );

/**
 * Create one demo category (if missing) and attach its photo (if it has none).
 *
 * @param string $name  Category name.
 * @param string $photo Filename under assets/images/demo/.
 * @return int Term id, or 0 on failure.
 */
function rentiva_import_demo_category( $name, $photo ) {
	$term_id = rentiva_get_or_create_term( $name, 'rbfw_item_caregory' );

	if ( $term_id && ! rentiva_demo_attachment_is_valid( get_term_meta( $term_id, 'rentiva_category_image_id', true ) ) ) {
		$image_id = rentiva_sideload_demo_photo( $photo, $name . ' category' );
		if ( $image_id ) {
			update_term_meta( $term_id, 'rentiva_category_image_id', $image_id );
		}
	}

	return $term_id;
}

/**
 * Create one demo `rbfw_item` if no item with its title exists yet, then fill
 * in anything it's missing. Every field below is only written when it's
 * empty, so the same code builds a brand-new item and repairs a demo item an
 * earlier, interrupted, or broken import left incomplete — a missing
 * category, location, price, spec list, stock, FAQ, or photo whose file is
 * gone — without ever overwriting a value an admin has since changed.
 *
 * @param array $item One entry from rentiva_demo_items().
 * @return void
 */
function rentiva_import_demo_item( $item ) {
	$post_id = rentiva_find_demo_item( $item['title'] );

	if ( ! $post_id ) {
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
			return;
		}
	}

	if ( array_key_exists( $item['category'], rentiva_demo_categories() ) ) {
		rentiva_demo_fill_term( $post_id, $item['category'], 'rbfw_item_caregory' );

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
		if ( empty( get_post_meta( $post_id, 'rbfw_categories', true ) ) ) {
			update_post_meta( $post_id, 'rbfw_categories', array( $item['category'] ) );
		}
	}

	if ( in_array( $item['location'], rentiva_demo_locations(), true ) ) {
		rentiva_demo_fill_term( $post_id, $item['location'], 'rbfw_item_location' );
	}

	$meta = array(
		'rbfw_item_type'          => 'bike_car_md',
		'rbfw_enable_daily_rate'  => 'yes',
		'rbfw_daily_rate'         => (float) $item['daily_rate'],
		'rbfw_enable_hourly_rate' => 'no',
		// Without a quantity, the plugin's own stock logic treats the item as
		// zero in stock — "out of stock" no matter when it was created. '10'
		// matches the plugin's own bundled demo importer
		// (inc/rbfw_import_demo.php) exactly.
		'rbfw_item_stock_quantity' => '10',
	);
	if ( ! empty( $item['weekly_rate'] ) ) {
		$meta['rbfw_enable_weekly_rate'] = 'yes';
		$meta['rbfw_weekly_rate']        = (float) $item['weekly_rate'];
	}
	foreach ( $meta as $key => $value ) {
		if ( '' === get_post_meta( $post_id, $key, true ) ) {
			update_post_meta( $post_id, $key, $value );
		}
	}

	if ( empty( get_post_meta( $post_id, 'rbfw_feature_category', true ) ) ) {
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

	if ( ! empty( $item['photo'] ) && ! rentiva_demo_attachment_is_valid( get_post_thumbnail_id( $post_id ) ) ) {
		$image_id = rentiva_sideload_demo_photo( $item['photo'], $item['title'] );
		if ( $image_id ) {
			set_post_thumbnail( $post_id, $image_id );
		}
	}

	// Only when the item has no FAQ entries at all, so real FAQs an admin has
	// written are never replaced. Renders via the plugin's own FAQ accordion
	// (admin/settings/Faq.php, `mep_event_faq` post meta) — nothing in the
	// theme renders these.
	if ( empty( get_post_meta( $post_id, 'mep_event_faq', true ) ) ) {
		update_post_meta( $post_id, 'mep_event_faq', rentiva_demo_faqs( $item['title'] ) );
		update_post_meta( $post_id, 'rbfw_enable_faq_content', 'yes' );
	}
}

/**
 * The id of the demo `rbfw_item` with this exact title (any status except
 * trash), or 0.
 *
 * @param string $title
 * @return int
 */
function rentiva_find_demo_item( $title ) {
	$existing = new WP_Query(
		array(
			'post_type'              => 'rbfw_item',
			'post_status'            => 'any',
			'title'                  => $title,
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	return $existing->have_posts() ? (int) $existing->posts[0] : 0;
}

/**
 * Assign a term (by name, created if needed) to a post that has no term at
 * all in that taxonomy yet — one an admin has already assigned is kept.
 *
 * @param int    $post_id
 * @param string $name
 * @param string $taxonomy
 * @return void
 */
function rentiva_demo_fill_term( $post_id, $name, $taxonomy ) {
	$current = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
	if ( is_wp_error( $current ) || ! empty( $current ) ) {
		return;
	}

	$term_id = rentiva_get_or_create_term( $name, $taxonomy );
	if ( $term_id ) {
		wp_set_object_terms( $post_id, $term_id, $taxonomy );
	}
}

/**
 * Whether an attachment id points at a real attachment whose file is still on
 * disk — a stale id (deleted attachment, or uploads wiped while the database
 * kept the row) counts as missing, so the importer sideloads a fresh photo.
 *
 * @param int|string $attachment_id
 * @return bool
 */
function rentiva_demo_attachment_is_valid( $attachment_id ) {
	$attachment_id = (int) $attachment_id;
	if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
		return false;
	}

	$file = get_attached_file( $attachment_id );

	return $file && file_exists( $file );
}

/**
 * Whether the booking plugin is loaded enough to import into: its `rbfw_item`
 * post type AND both of its taxonomies. The plugin deliberately skips
 * registering its taxonomies under WP-CLI (admin/taxonomy_register.php) while
 * still registering the post type, so rentiva_has_booking_plugin() alone
 * passes there — and an import would silently create every item with no
 * category or location, and no category images.
 *
 * @return bool
 */
function rentiva_demo_import_ready() {
	return rentiva_has_booking_plugin() && taxonomy_exists( 'rbfw_item_caregory' ) && taxonomy_exists( 'rbfw_item_location' );
}

/**
 * What's still missing from the demo catalog — empty when every demo
 * category (with its image), location, and rental item (with its category,
 * location and photo) is really there. The final `finish` step runs this, so
 * the import is only marked done when it actually is, and Setup's Step 2 shows
 * it for a site an earlier import left incomplete.
 *
 * @return string[] One human-readable line per problem.
 */
function rentiva_get_demo_import_problems() {
	$problems = array();

	foreach ( array_keys( rentiva_demo_categories() ) as $name ) {
		$term = get_term_by( 'name', $name, 'rbfw_item_caregory' );
		if ( ! $term ) {
			/* translators: %s: category name. */
			$problems[] = sprintf( __( 'Category "%s" is missing.', 'rentiva' ), $name );
		} elseif ( ! rentiva_demo_attachment_is_valid( get_term_meta( $term->term_id, 'rentiva_category_image_id', true ) ) ) {
			/* translators: %s: category name. */
			$problems[] = sprintf( __( 'Category "%s" has no image.', 'rentiva' ), $name );
		}
	}

	foreach ( rentiva_demo_locations() as $name ) {
		if ( ! get_term_by( 'name', $name, 'rbfw_item_location' ) ) {
			/* translators: %s: pickup location name. */
			$problems[] = sprintf( __( 'Pickup location "%s" is missing.', 'rentiva' ), $name );
		}
	}

	foreach ( rentiva_demo_items() as $item ) {
		$post_id = rentiva_find_demo_item( $item['title'] );
		if ( ! $post_id ) {
			/* translators: %s: rental item title. */
			$problems[] = sprintf( __( 'Rental item "%s" is missing.', 'rentiva' ), $item['title'] );
			continue;
		}

		$term_checks = array(
			/* translators: %s: rental item title. */
			'rbfw_item_caregory' => __( 'Rental item "%s" has no category.', 'rentiva' ),
			/* translators: %s: rental item title. */
			'rbfw_item_location' => __( 'Rental item "%s" has no pickup location.', 'rentiva' ),
		);
		foreach ( $term_checks as $taxonomy => $message ) {
			$terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
			if ( is_wp_error( $terms ) || empty( $terms ) ) {
				$problems[] = sprintf( $message, $item['title'] );
			}
		}

		if ( ! empty( $item['photo'] ) && ! rentiva_demo_attachment_is_valid( get_post_thumbnail_id( $post_id ) ) ) {
			/* translators: %s: rental item title. */
			$problems[] = sprintf( __( 'Rental item "%s" has no photo.', 'rentiva' ), $item['title'] );
		}
	}

	return $problems;
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
	foreach ( rentiva_demo_homepage_images() as $key => $filename ) {
		rentiva_import_demo_homepage_image( $key, $filename );
	}
}

/**
 * Sideload one homepage photo into its `rentiva_settings` key, unless that
 * key already has an image whose file still exists — see
 * rentiva_import_demo_homepage_images().
 *
 * @param string $key      `rentiva_settings` key, e.g. 'hero_image_id'.
 * @param string $filename Filename under assets/images/demo/.
 * @return void
 */
function rentiva_import_demo_homepage_image( $key, $filename ) {
	$settings = get_option( 'rentiva_settings', array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	if ( ! empty( $settings[ $key ] ) && rentiva_demo_attachment_is_valid( $settings[ $key ] ) ) {
		return;
	}

	$image_id = rentiva_sideload_demo_photo( $filename, $key );
	if ( $image_id ) {
		$settings[ $key ] = $image_id;
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
