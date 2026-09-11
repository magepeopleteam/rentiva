<?php
/**
 * Rentiva_Rental_Adapter — the ONLY code in this theme allowed to read
 * "Booking and Rental Manager for WooCommerce" (RBFW_*) internals.
 *
 * Every template, Elementor widget, and AJAX handler goes through this
 * class instead of calling `get_post_meta( $id, 'rbfw_...' )` directly, so
 * there is exactly one place that knows RBFW's meta keys, taxonomy slugs
 * (including the permanent `rbfw_item_caregory` typo) and template-override
 * mechanism. See docs/booking-integration.md for the full picture.
 *
 * This class only ever produces DISPLAY estimates for cards/hero/detail
 * pages. It never computes an actual checkout price or reserves stock —
 * that stays exclusively inside the plugin's own price-calculation
 * functions and its real booking form (see render_booking_form()).
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rentiva_Rental_Adapter {

	const TAXONOMY_CATEGORY = 'rbfw_item_caregory'; // Verbatim — this typo is permanent in the plugin, see docs/booking-integration.md.
	const TAXONOMY_LOCATION = 'rbfw_item_location';

	/* ---------------------------------------------------------------
	 * Bootstrap
	 * ------------------------------------------------------------- */

	/**
	 * Whether the plugin is active and its core classes are loaded.
	 *
	 * @return bool
	 */
	public static function is_active() {
		return post_type_exists( 'rbfw_item' ) && class_exists( 'RBFW_Function' ) && class_exists( 'RBFW_Frontend' );
	}

	/**
	 * The plugin's rentable-item post type slug.
	 *
	 * @return string
	 */
	public static function get_cpt_name() {
		if ( class_exists( 'RBFW_Function' ) && method_exists( 'RBFW_Function', 'get_cpt_name' ) ) {
			return RBFW_Function::get_cpt_name();
		}
		return 'rbfw_item';
	}

	/* ---------------------------------------------------------------
	 * Item meta / type / taxonomy
	 * ------------------------------------------------------------- */

	/**
	 * One of: bike_car_sd, appointment, bike_car_md, equipment, dress,
	 * others, resort, multiple_items.
	 *
	 * @param int $item_id
	 * @return string
	 */
	public static function get_item_type( $item_id ) {
		if ( class_exists( 'RBFW_Frontend' ) ) {
			return RBFW_Frontend::get_rent_type( $item_id );
		}
		$type = get_post_meta( $item_id, 'rbfw_item_type', true );
		return $type ? $type : 'bike_car_sd';
	}

	/**
	 * Human-readable label for get_item_type().
	 *
	 * @param int $item_id
	 * @return string
	 */
	public static function get_item_type_label( $item_id ) {
		$labels = array(
			'bike_car_sd'    => __( 'Hourly / Single Day', 'rentiva' ),
			'appointment'    => __( 'Appointment', 'rentiva' ),
			'bike_car_md'    => __( 'Multi-Day', 'rentiva' ),
			'equipment'      => __( 'Equipment', 'rentiva' ),
			'dress'          => __( 'Dress', 'rentiva' ),
			'others'         => __( 'Rental', 'rentiva' ),
			'resort'         => __( 'Resort / Room', 'rentiva' ),
			'multiple_items' => __( 'Multi-Item', 'rentiva' ),
		);
		$type = self::get_item_type( $item_id );
		return isset( $labels[ $type ] ) ? $labels[ $type ] : __( 'Rental', 'rentiva' );
	}

	/**
	 * The single-item template variant this item resolves to: single-day,
	 * multi-day, resort, or multiple-items. Used only to decide which
	 * plugin form partial to `include` in render_booking_form() — never
	 * to duplicate the plugin's own dispatch logic elsewhere.
	 *
	 * @param int $item_id
	 * @return string
	 */
	public static function get_rent_type_template( $item_id ) {
		if ( class_exists( 'RBFW_Frontend' ) ) {
			return RBFW_Frontend::get_rent_type_template( $item_id );
		}
		return 'multi-day';
	}

	/**
	 * Primary category term (or null).
	 *
	 * @param int $item_id
	 * @return WP_Term|null
	 */
	public static function get_primary_category( $item_id ) {
		$terms = self::get_categories( $item_id );
		return ! empty( $terms ) ? $terms[0] : null;
	}

	/**
	 * All category terms.
	 *
	 * @param int $item_id
	 * @return WP_Term[]
	 */
	public static function get_categories( $item_id ) {
		$terms = get_the_terms( $item_id, self::TAXONOMY_CATEGORY );
		return is_array( $terms ) ? $terms : array();
	}

	/**
	 * All location (pickup area) terms.
	 *
	 * @param int $item_id
	 * @return WP_Term[]
	 */
	public static function get_locations( $item_id ) {
		$terms = get_the_terms( $item_id, self::TAXONOMY_LOCATION );
		return is_array( $terms ) ? $terms : array();
	}

	/**
	 * Comma-separated location names for card/detail display.
	 *
	 * @param int $item_id
	 * @return string
	 */
	public static function get_location_label( $item_id ) {
		$terms = self::get_locations( $item_id );
		if ( empty( $terms ) ) {
			return '';
		}
		return implode( ', ', wp_list_pluck( $terms, 'name' ) );
	}

	/**
	 * Short subtitle shown under the item title (e.g. "Mountain Bike").
	 *
	 * Falls back to the primary category name when the plugin has no
	 * subtitle of its own set for this item.
	 *
	 * @param int $item_id
	 * @return string
	 */
	public static function get_subtitle( $item_id ) {
		$subtitle = get_post_meta( $item_id, 'rbfw_item_sub_title', true );
		if ( $subtitle ) {
			return $subtitle;
		}
		$category = self::get_primary_category( $item_id );
		return $category ? $category->name : '';
	}

	/* ---------------------------------------------------------------
	 * Pricing — display estimates only, see class docblock.
	 * ------------------------------------------------------------- */

	/**
	 * Which of hourly/daily/weekly/monthly rates are enabled for this item.
	 *
	 * @param int $item_id
	 * @return array<string,bool>
	 */
	public static function get_enabled_pricing_types( $item_id ) {
		$periods = array( 'hourly', 'daily', 'weekly', 'monthly' );
		$enabled = array();
		foreach ( $periods as $period ) {
			$enabled[ $period ] = 'yes' === get_post_meta( $item_id, "rbfw_enable_{$period}_rate", true );
		}
		return $enabled;
	}

	/**
	 * Raw rate for one period ('hourly'|'daily'|'weekly'|'monthly').
	 *
	 * @param int    $item_id
	 * @param string $period
	 * @return float|null
	 */
	public static function get_rate( $item_id, $period ) {
		$value = get_post_meta( $item_id, "rbfw_{$period}_rate", true );
		return ( '' === $value || null === $value ) ? null : (float) $value;
	}

	/**
	 * Lowest configured price across the single-day/appointment per-type
	 * price table (`rbfw_bike_car_sd_data`), used only for those two item
	 * types since they don't use the flat hourly/daily/weekly/monthly rates.
	 *
	 * @param int $item_id
	 * @return float|null
	 */
	protected static function get_single_day_min_price( $item_id ) {
		$rows = get_post_meta( $item_id, 'rbfw_bike_car_sd_data', true );
		if ( ! is_array( $rows ) || empty( $rows ) ) {
			return null;
		}
		$prices = array();
		foreach ( $rows as $row ) {
			if ( isset( $row['price'] ) && is_numeric( $row['price'] ) ) {
				$prices[] = (float) $row['price'];
			}
		}
		return empty( $prices ) ? null : min( $prices );
	}

	/**
	 * The single funnel every card/hero/detail price block calls: the
	 * cheapest applicable rate for this item, formatted for display, plus
	 * its unit label. Never used for checkout math.
	 *
	 * @param int $item_id
	 * @return array{amount:float|null,formatted:string,unit:string}
	 */
	public static function get_display_price( $item_id ) {
		$type = self::get_item_type( $item_id );

		if ( in_array( $type, array( 'bike_car_sd', 'appointment' ), true ) ) {
			$amount = self::get_single_day_min_price( $item_id );
			return array(
				'amount'    => $amount,
				'formatted' => null === $amount ? '' : rentiva_format_price( $amount ),
				'unit'      => __( '/ booking', 'rentiva' ),
			);
		}

		$enabled     = self::get_enabled_pricing_types( $item_id );
		$unit_labels = array(
			'daily'   => __( '/ day', 'rentiva' ),
			'hourly'  => __( '/ hour', 'rentiva' ),
			'weekly'  => __( '/ week', 'rentiva' ),
			'monthly' => __( '/ month', 'rentiva' ),
		);

		// Daily first — matches the mockup's card/detail pricing convention —
		// then fall back to whichever other period is actually enabled.
		foreach ( array( 'daily', 'hourly', 'weekly', 'monthly' ) as $period ) {
			if ( empty( $enabled[ $period ] ) ) {
				continue;
			}
			$rate = self::get_rate( $item_id, $period );
			if ( null !== $rate ) {
				return array(
					'amount'    => $rate,
					'formatted' => rentiva_format_price( $rate ),
					'unit'      => $unit_labels[ $period ],
				);
			}
		}

		return array(
			'amount'    => null,
			'formatted' => '',
			'unit'      => '',
		);
	}

	/**
	 * A secondary price to show alongside the primary one (e.g. "$85/week"
	 * next to "$18/day"), matching the mockup's DetailPage price block.
	 * Returns null when only one pricing period is configured.
	 *
	 * @param int $item_id
	 * @return array{amount:float,formatted:string,unit:string}|null
	 */
	public static function get_secondary_price( $item_id ) {
		$primary = self::get_display_price( $item_id );
		$enabled = self::get_enabled_pricing_types( $item_id );
		$unit_labels = array(
			'daily'   => __( '/ day', 'rentiva' ),
			'hourly'  => __( '/ hour', 'rentiva' ),
			'weekly'  => __( '/ week', 'rentiva' ),
			'monthly' => __( '/ month', 'rentiva' ),
		);

		foreach ( array( 'weekly', 'monthly', 'daily', 'hourly' ) as $period ) {
			if ( empty( $enabled[ $period ] ) || $unit_labels[ $period ] === $primary['unit'] ) {
				continue;
			}
			$rate = self::get_rate( $item_id, $period );
			if ( null !== $rate ) {
				return array(
					'amount'    => $rate,
					'formatted' => rentiva_format_price( $rate ),
					'unit'      => $unit_labels[ $period ],
				);
			}
		}
		return null;
	}

	/**
	 * Whether a security deposit applies, and its display label
	 * (e.g. "$50 refundable deposit" / "10% refundable deposit").
	 *
	 * @param int $item_id
	 * @return bool
	 */
	public static function has_security_deposit( $item_id ) {
		return 'yes' === get_post_meta( $item_id, 'rbfw_enable_security_deposit', true );
	}

	/**
	 * @param int $item_id
	 * @return string
	 */
	public static function get_security_deposit_label( $item_id ) {
		if ( ! self::has_security_deposit( $item_id ) ) {
			return '';
		}

		$custom_label = get_post_meta( $item_id, 'rbfw_security_deposit_label', true );
		if ( $custom_label ) {
			return $custom_label;
		}

		$amount = get_post_meta( $item_id, 'rbfw_security_deposit_amount', true );
		$type   = get_post_meta( $item_id, 'rbfw_security_deposit_type', true );

		if ( '' === $amount ) {
			return __( 'Refundable security deposit required', 'rentiva' );
		}

		if ( 'percentage' === $type ) {
			return sprintf(
				/* translators: %s: percentage value */
				__( '%s%% refundable deposit', 'rentiva' ),
				esc_html( $amount )
			);
		}

		return sprintf(
			/* translators: %s: formatted amount */
			__( '%s refundable deposit', 'rentiva' ),
			wp_strip_all_tags( rentiva_format_price( $amount ) )
		);
	}

	/* ---------------------------------------------------------------
	 * Availability
	 * ------------------------------------------------------------- */

	/**
	 * @param int $item_id
	 * @return int|null Null when the plugin doesn't track a flat stock number for this item's type.
	 */
	public static function get_stock_quantity( $item_id ) {
		$qty = get_post_meta( $item_id, 'rbfw_item_stock_quantity', true );
		return ( '' === $qty || null === $qty ) ? null : (int) $qty;
	}

	/**
	 * Display-only availability label. Real per-date availability is only
	 * known by the plugin's own AJAX stock-check at booking time.
	 *
	 * @param int $item_id
	 * @return string 'in-stock'|'low-stock'|'sold-out'|'' (unknown)
	 */
	public static function get_availability_status( $item_id ) {
		$qty = self::get_stock_quantity( $item_id );
		if ( null === $qty ) {
			return '';
		}
		if ( $qty <= 0 ) {
			return 'sold-out';
		}
		if ( $qty <= 3 ) {
			return 'low-stock';
		}
		return 'in-stock';
	}

	/**
	 * Human label for get_availability_status().
	 *
	 * @param int $item_id
	 * @return string
	 */
	public static function get_availability_label( $item_id ) {
		$labels = array(
			'in-stock'  => __( 'Available', 'rentiva' ),
			'low-stock' => __( 'Limited availability', 'rentiva' ),
			'sold-out'  => __( 'Currently unavailable', 'rentiva' ),
		);
		$status = self::get_availability_status( $item_id );
		return isset( $labels[ $status ] ) ? $labels[ $status ] : '';
	}

	/**
	 * Blackout date ranges configured on this item, for a read-only
	 * "unavailable dates" note — the plugin's own datepicker (embedded via
	 * render_booking_form()) is the authority on live availability.
	 *
	 * @param int $item_id
	 * @return array<int,array{from:string,to:string}>
	 */
	public static function get_off_dates( $item_id ) {
		if ( function_exists( 'rbfw_off_dates' ) ) {
			$dates = rbfw_off_dates( $item_id );
			if ( is_array( $dates ) ) {
				return $dates;
			}
		}

		$raw = get_post_meta( $item_id, 'rbfw_offday_range', true );
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$ranges = array();
		foreach ( $raw as $row ) {
			if ( isset( $row['from_date'], $row['to_date'] ) ) {
				$ranges[] = array( 'from' => $row['from_date'], 'to' => $row['to_date'] );
			}
		}
		return $ranges;
	}

	/* ---------------------------------------------------------------
	 * Gallery
	 * ------------------------------------------------------------- */

	/**
	 * @param int    $item_id
	 * @param string $size
	 * @return string
	 */
	public static function get_thumbnail_url( $item_id, $size = 'rentiva-card' ) {
		$url = get_the_post_thumbnail_url( $item_id, $size );
		return $url ? $url : '';
	}

	/**
	 * @param int $item_id
	 * @return int
	 */
	public static function get_thumbnail_id( $item_id ) {
		return (int) get_post_thumbnail_id( $item_id );
	}

	/**
	 * Attachment IDs for the item's gallery, thumbnail first.
	 *
	 * @param int $item_id
	 * @return int[]
	 */
	public static function get_gallery_ids( $item_id ) {
		$ids = array();

		$thumbnail_id = self::get_thumbnail_id( $item_id );
		if ( $thumbnail_id ) {
			$ids[] = $thumbnail_id;
		}

		$raw = get_post_meta( $item_id, 'rbfw_gallery_images', true );
		if ( is_string( $raw ) && $raw ) {
			$raw = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
		}
		if ( is_array( $raw ) ) {
			foreach ( $raw as $gallery_id ) {
				$gallery_id = (int) $gallery_id;
				if ( $gallery_id && ! in_array( $gallery_id, $ids, true ) ) {
					$ids[] = $gallery_id;
				}
			}
		}

		return $ids;
	}

	/* ---------------------------------------------------------------
	 * Specs — reuses the plugin's own "Feature List" field
	 * (`rbfw_feature_category`, edited via the plugin's Modern Editor)
	 * rather than inventing a parallel theme-owned meta field.
	 * ------------------------------------------------------------- */

	/**
	 * Raw feature categories from the plugin's Feature List field, each
	 * shaped as {title, features[]}. Site owners typically create one
	 * category for specs ("Frame: Aluminum" style rows) and, optionally, a
	 * second one for "What's Included" — see get_specs()/get_included_items()
	 * and docs/customization.md.
	 *
	 * @param int $item_id
	 * @return array<int,array{title:string,features:string[]}>
	 */
	protected static function get_feature_categories( $item_id ) {
		if ( ! function_exists( 'rbfw_get_feature_category_meta' ) ) {
			return array();
		}

		$raw = rbfw_get_feature_category_meta( $item_id );
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$categories = array();
		foreach ( $raw as $category ) {
			$features = isset( $category['cat_features'] ) && is_array( $category['cat_features'] ) ? $category['cat_features'] : array();
			$titles   = array();
			foreach ( $features as $feature ) {
				$title = isset( $feature['title'] ) ? trim( $feature['title'] ) : '';
				if ( '' !== $title ) {
					$titles[] = $title;
				}
			}
			if ( ! empty( $titles ) ) {
				$categories[] = array(
					'title'    => isset( $category['cat_title'] ) ? trim( $category['cat_title'] ) : '',
					'features' => $titles,
				);
			}
		}

		return $categories;
	}

	/**
	 * Label/value spec rows for the "Key Specifications" grid, from the
	 * FIRST feature category. Site owners enter each row as "Label: Value"
	 * (e.g. "Frame: Aluminum") — see docs/customization.md. A feature
	 * entered without a colon is shown as a single label.
	 *
	 * @param int $item_id
	 * @return array<int,array{label:string,value:string}>
	 */
	public static function get_specs( $item_id ) {
		$categories = self::get_feature_categories( $item_id );
		if ( empty( $categories ) ) {
			return array();
		}

		$specs = array();
		foreach ( $categories[0]['features'] as $title ) {
			if ( false !== strpos( $title, ':' ) ) {
				list( $label, $value ) = array_map( 'trim', explode( ':', $title, 2 ) );
			} else {
				$label = $title;
				$value = '';
			}
			$specs[] = array(
				'label' => $label,
				'value' => $value,
			);
		}

		return $specs;
	}

	/**
	 * @param int $item_id
	 * @return bool
	 */
	public static function has_specs( $item_id ) {
		return ! empty( self::get_specs( $item_id ) );
	}

	/**
	 * "What's Included" checklist items, from the SECOND feature category
	 * (if one exists) — see docs/customization.md.
	 *
	 * @param int $item_id
	 * @return string[]
	 */
	public static function get_included_items( $item_id ) {
		$categories = self::get_feature_categories( $item_id );
		return isset( $categories[1] ) ? $categories[1]['features'] : array();
	}

	/* ---------------------------------------------------------------
	 * Reviews — native WP comments on the real rbfw_item post, enriched
	 * with the theme's own `rentiva_rating` comment meta. Never the
	 * plugin's hidden shadow WooCommerce product (which the plugin
	 * itself hard-404s on direct visit — see docs/booking-integration.md).
	 * ------------------------------------------------------------- */

	/**
	 * @param int $item_id
	 * @return int
	 */
	public static function get_review_count( $item_id ) {
		return (int) get_comments_number( $item_id );
	}

	/**
	 * Average of every `rentiva_rating` comment-meta value on this item's
	 * approved comments.
	 *
	 * @param int $item_id
	 * @return float
	 */
	public static function get_average_rating( $item_id ) {
		$comments = get_comments(
			array(
				'post_id' => $item_id,
				'status'  => 'approve',
			)
		);

		$ratings = array();
		foreach ( $comments as $comment ) {
			$rating = (int) get_comment_meta( $comment->comment_ID, 'rentiva_rating', true );
			if ( $rating >= 1 && $rating <= 5 ) {
				$ratings[] = $rating;
			}
		}

		if ( empty( $ratings ) ) {
			return 0.0;
		}

		return round( array_sum( $ratings ) / count( $ratings ), 1 );
	}

	/**
	 * Render the review list + form for this item via WordPress's native
	 * comments_template() (which loads the theme's comments.php — see
	 * inc/template-functions.php's rentiva_review_comment() callback).
	 *
	 * @param int $item_id
	 * @return void
	 */
	public static function render_reviews( $item_id ) {
		comments_template();
	}

	/* ---------------------------------------------------------------
	 * Booking form — plugin-owned, thin delegation only. See
	 * docs/booking-integration.md for why no filter override is needed:
	 * RBFW_Function::get_template_path() already resolves the theme's
	 * templates/single/single-rbfw.php first, so this method only has to
	 * embed the correct inner form partial for the item's type.
	 * ------------------------------------------------------------- */

	/**
	 * Include the plugin's real registration/add-to-cart form for this
	 * item — unmodified date-pickers, AJAX price calc, stock checks, nonces
	 * and cart submission all keep working exactly as the plugin ships them.
	 *
	 * @param int $item_id
	 * @return void
	 */
	public static function render_booking_form( $item_id ) {
		if ( ! self::is_active() || ! class_exists( 'RBFW_Function' ) ) {
			return;
		}

		$file_name = self::get_rent_type_template( $item_id );
		$template  = RBFW_Frontend::get_template_name( $item_id );
		$path      = RBFW_Function::get_template_path( "forms/{$file_name}-registration.php" );

		if ( ! is_readable( $path ) ) {
			// Some plugin versions key the form partial by template variant too.
			$path = RBFW_Function::get_template_path( "single/{$template}/forms/{$file_name}-registration.php" );
		}

		if ( ! is_readable( $path ) ) {
			return;
		}

		global $post;
		$post    = get_post( $item_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- required so the plugin's form partial (which reads $post/$post_id like its own single-*.php wrappers do) sees the right item.
		$post_id = $item_id;
		setup_postdata( $post );

		// Reproduce the exact `.mp_right_section` > `.rbfw-booking-form`
		// wrapper every plugin single-item template
		// (templates/single/{default,muffin}/*.php) puts around this same
		// form include. A large share of the plugin's own CSS (rbfw_style.css)
		// — book-now button width/padding/radius, the multi-items grid, etc. —
		// is keyed off these specific ancestor classes, so omitting them left
		// the form working but visually broken even with zero theme CSS
		// involved. `rbfw_multi_items_right` / `single-day-booking` are the
		// same extra per-type classes those templates add.
		$section_class = 'mp_right_section';
		if ( 'multi-items' === $file_name ) {
			$section_class .= ' rbfw_multi_items_right';
		}

		$form_class = 'rbfw-booking-form';
		if ( 'single-day' === $file_name ) {
			$form_class .= ' single-day-booking';
		}

		printf( '<div class="%s">', esc_attr( $section_class ) );
		do_action( 'booking_form_header', $post_id );
		printf( '<div class="%s" id="rbfw_default_booking_form">', esc_attr( $form_class ) );
		include $path; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- resolved exclusively via RBFW_Function::get_template_path(), not user input.
		echo '</div></div>';

		wp_reset_postdata();
	}

	/**
	 * Fallback embedding path for item types whose form partial expects to
	 * be invoked through the plugin's own shortcode context.
	 *
	 * @param int $item_id
	 * @return string
	 */
	public static function get_add_to_cart_shortcode( $item_id ) {
		return do_shortcode( '[rent-add-to-cart id="' . absint( $item_id ) . '"]' );
	}

	/* ---------------------------------------------------------------
	 * Card normalization / queries
	 * ------------------------------------------------------------- */

	/**
	 * The single funnel struct every rental-card.php / Elementor grid widget
	 * consumes.
	 *
	 * @param int|WP_Post $item
	 * @return array<string,mixed>
	 */
	public static function get_item_card_data( $item ) {
		$item_id = is_object( $item ) ? $item->ID : (int) $item;
		$price   = self::get_display_price( $item_id );

		return array(
			'id'         => $item_id,
			'title'      => get_the_title( $item_id ),
			'url'        => get_permalink( $item_id ),
			'image_id'   => self::get_thumbnail_id( $item_id ),
			'type'       => self::get_subtitle( $item_id ),
			'rating'     => self::get_average_rating( $item_id ),
			'reviews'    => self::get_review_count( $item_id ),
			'location'   => self::get_location_label( $item_id ),
			'price'      => $price['formatted'],
			'price_unit' => $price['unit'],
		);
	}

	/**
	 * Card-shaped arrays for a homepage/section context.
	 *
	 * @param int    $count
	 * @param string $context 'popular'|'newest'.
	 * @param array  $args    Extra WP_Query args.
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_items_for_cards( $count = 4, $context = 'popular', $args = array() ) {
		if ( ! self::is_active() ) {
			return array();
		}

		$query_args = wp_parse_args(
			$args,
			array(
				'post_type'      => self::get_cpt_name(),
				'post_status'    => 'publish',
				'posts_per_page' => $count,
				'orderby'        => 'newest' === $context ? 'date' : 'comment_count',
				'order'          => 'DESC',
			)
		);

		$query = new WP_Query( $query_args );
		$cards = array();

		foreach ( $query->posts as $post_item ) {
			$cards[] = self::get_item_card_data( $post_item );
		}

		wp_reset_postdata();

		return $cards;
	}

	/**
	 * Card-shaped "similar rentals" for the single-item page — same primary
	 * category as $item_id, excluding itself, falling back to newest items
	 * when the item has no category or no siblings.
	 *
	 * @param int $item_id
	 * @param int $count
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_similar_items( $item_id, $count = 4 ) {
		if ( ! self::is_active() ) {
			return array();
		}

		$category   = self::get_primary_category( $item_id );
		$query_args = array(
			'post_type'      => self::get_cpt_name(),
			'post_status'    => 'publish',
			'posts_per_page' => $count,
			'post__not_in'   => array( $item_id ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( $category ) {
			$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- single fixed-category lookup for a "similar items" widget, not user-driven.
				array(
					'taxonomy' => self::TAXONOMY_CATEGORY,
					'field'    => 'term_id',
					'terms'    => $category->term_id,
				),
			);
		}

		$query = new WP_Query( $query_args );
		$cards = array();
		foreach ( $query->posts as $post_item ) {
			$cards[] = self::get_item_card_data( $post_item );
		}
		wp_reset_postdata();

		return $cards;
	}

	/**
	 * General-purpose item query, respecting category/location filters and
	 * an optional free-text term — used by the archive template.
	 *
	 * @param array $args {
	 *     @type string|string[] $category Category term slug(s).
	 *     @type string|string[] $location Location term slug(s).
	 *     @type string          $search   Free-text search term.
	 *     @type string          $orderby  WP_Query 'orderby' value. Default 'date'.
	 *     @type string          $order    'ASC'|'DESC'. Default 'DESC'.
	 *     @type int             $paged
	 *     @type int             $per_page
	 * }
	 * @return WP_Query
	 */
	public static function query_items( $args = array() ) {
		$defaults = array(
			'category' => '',
			'location' => '',
			'search'   => '',
			'orderby'  => 'date',
			'order'    => 'DESC',
			'paged'    => 1,
			'per_page' => 12,
		);
		$args = wp_parse_args( $args, $defaults );

		$query_args = array(
			'post_type'      => self::get_cpt_name(),
			'post_status'    => 'publish',
			'posts_per_page' => $args['per_page'],
			'paged'          => $args['paged'],
			'orderby'        => $args['orderby'],
			'order'          => $args['order'],
		);

		if ( $args['search'] ) {
			$query_args['s'] = $args['search'];
		}

		$tax_query = array();
		if ( $args['category'] ) {
			$tax_query[] = array(
				'taxonomy' => self::TAXONOMY_CATEGORY,
				'field'    => 'slug',
				'terms'    => $args['category'],
			);
		}
		if ( $args['location'] ) {
			$tax_query[] = array(
				'taxonomy' => self::TAXONOMY_LOCATION,
				'field'    => 'slug',
				'terms'    => $args['location'],
			);
		}
		if ( ! empty( $tax_query ) ) {
			$query_args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- bounded by admin-configured category/location filters, not arbitrary user input volume.
		}

		return new WP_Query( $query_args );
	}

	/**
	 * Card-shaped search results — used by the header quick-search and, as
	 * a fallback, anywhere the plugin's own `[search-result]` shortcode
	 * output isn't embedded directly.
	 *
	 * @param array $args See query_items().
	 * @return array<int,array<string,mixed>>
	 */
	public static function search_items( $args = array() ) {
		$query = self::query_items( $args );
		$cards = array();
		foreach ( $query->posts as $post_item ) {
			$cards[] = self::get_item_card_data( $post_item );
		}
		wp_reset_postdata();
		return $cards;
	}

	/**
	 * Lightweight autocomplete suggestions for the header/hero search — one
	 * thumbnail + title + type/price line per row, enough for the dropdown
	 * to render a real card without a second round-trip per suggestion.
	 *
	 * @param string $term
	 * @param int    $limit
	 * @return array<int,array{id:int,title:string,url:string,image:string,type:string,price:string}>
	 */
	public static function suggest_items( $term, $limit = 8 ) {
		if ( ! self::is_active() || '' === trim( $term ) ) {
			return array();
		}

		$query = new WP_Query(
			array(
				'post_type'      => self::get_cpt_name(),
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				's'              => $term,
				'fields'         => 'ids',
			)
		);

		$results = array();
		foreach ( $query->posts as $post_id ) {
			$image_id = self::get_thumbnail_id( $post_id );
			$price    = self::get_display_price( $post_id );

			$results[] = array(
				'id'    => $post_id,
				'title' => get_the_title( $post_id ),
				'url'   => get_permalink( $post_id ),
				'image' => $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '',
				'type'  => self::get_subtitle( $post_id ),
				// get_display_price()'s 'formatted' is WooCommerce price HTML
				// (wc_price() markup, currency symbol included as an HTML
				// entity) — plain-text it here since the JS dropdown row
				// renders this via textContent, not innerHTML.
				'price' => $price['formatted'] ? html_entity_decode( wp_strip_all_tags( $price['formatted'] ), ENT_QUOTES, 'UTF-8' ) . ' ' . $price['unit'] : '',
			);
		}

		return $results;
	}
}
