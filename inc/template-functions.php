<?php
/**
 * Shared render-function layer.
 *
 * These are the single funnel points every classic PHP template AND every
 * Elementor widget call to render the same markup — nothing here queries
 * `rbfw_item` meta directly; that's Rentiva_Rental_Adapter's job
 * (inc/integrations/booking-plugin.php).
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Header: logo + primary navigation
 * ---------------------------------------------------------------------- */

/**
 * Output the site logo — a custom uploaded logo if set, otherwise the
 * theme's default green rounded mark + wordmark (matches
 * mockup/src/components/Header.tsx's <Logo> block exactly).
 *
 * @return void
 */
function rentiva_the_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-header__logo" rel="home">
		<span class="site-header__mark" aria-hidden="true">
			<svg width="18" height="18" viewBox="0 0 18 18" fill="none">
				<path d="M3 9C3 9 6 5 9 5C12 5 15 9 15 9C15 9 12 13 9 13C6 13 3 9 3 9Z" fill="white" fill-opacity="0.9"/>
				<circle cx="9" cy="9" r="2.5" fill="white"/>
			</svg>
		</span>
		<span class="site-header__logo-text"><?php bloginfo( 'name' ); ?></span>
	</a>
	<?php
}

/**
 * Fallback nav items shown when no "Primary Navigation" menu has been
 * assigned yet (Appearance → Menus) — keeps a fresh install matching the
 * mockup's Home / Explore Rentals / How It Works / About links out of the box.
 *
 * @return array<int,array{label:string,url:string}>
 */
function rentiva_default_primary_nav_items() {
	$about_page = get_page_by_path( 'about' );

	return array(
		array(
			'label' => __( 'Home', 'rentiva' ),
			'url'   => home_url( '/' ),
		),
		array(
			'label' => __( 'Explore Rentals', 'rentiva' ),
			'url'   => rentiva_get_rentals_page_url(),
		),
		array(
			'label' => __( 'How It Works', 'rentiva' ),
			'url'   => ( is_front_page() ? '#how-it-works' : home_url( '/#how-it-works' ) ),
		),
		array(
			'label' => __( 'About', 'rentiva' ),
			'url'   => $about_page ? get_permalink( $about_page ) : home_url( '/' ),
		),
	);
}

/**
 * Print the fallback nav links (used both for the desktop and mobile menus
 * when no "primary" menu is registered).
 *
 * @return void
 */
function rentiva_the_default_nav_links() {
	foreach ( rentiva_default_primary_nav_items() as $item ) {
		printf( '<a href="%1$s">%2$s</a>', esc_url( $item['url'] ), esc_html( $item['label'] ) );
	}
}

/**
 * Render the primary navigation menu, falling back to the default links.
 *
 * @param string $container_class Extra class for wp_nav_menu()'s container-less <ul>.
 * @return void
 */
function rentiva_primary_nav( $container_class = '' ) {
	$has_menu = has_nav_menu( 'primary' );

	if ( $has_menu ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '%3$s',
				'depth'          => 2,
			)
		);
		return;
	}

	rentiva_the_default_nav_links();
}

/**
 * URL for the header's "Sign In" action — WooCommerce My Account when active,
 * otherwise the login screen.
 *
 * @return string
 */
function rentiva_get_signin_url() {
	if ( rentiva_has_woocommerce() && function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( 'myaccount' );
		if ( $url ) {
			return $url;
		}
	}
	return wp_login_url();
}

/* -------------------------------------------------------------------------
 * Rental cards / grids — the single funnel every template and Elementor
 * widget uses to render item cards, so the markup never drifts between
 * Popular Rentals, the archive grid, and Similar Rentals.
 * ---------------------------------------------------------------------- */

/**
 * Bundled demo dataset shown when the booking plugin has no real `rbfw_item`
 * posts yet (fresh install, before the demo importer / real catalog exists) —
 * matches mockup/src/pages/HomePage.tsx's 4 demo products exactly so the
 * homepage never renders an empty/broken-looking grid out of the box.
 *
 * @return array<int,array<string,mixed>> Card-shaped arrays (see cards/rental-card.php).
 */
function rentiva_get_demo_rentals() {
	return array(
		array(
			'id'         => 0,
			'title'      => __( 'Explorer X1', 'rentiva' ),
			'type'       => __( 'Mountain Bike', 'rentiva' ),
			'url'        => rentiva_get_rentals_page_url(),
			'image_id'   => 0,
			'rating'     => 4.9,
			'reviews'    => 87,
			'location'   => __( 'Dhaka', 'rentiva' ),
			'price'      => rentiva_format_price( 18 ),
			'price_unit' => __( '/ day', 'rentiva' ),
		),
		array(
			'id'         => 0,
			'title'      => __( 'Urban Cruiser', 'rentiva' ),
			'type'       => __( 'City Bike', 'rentiva' ),
			'url'        => rentiva_get_rentals_page_url(),
			'image_id'   => 0,
			'rating'     => 4.8,
			'reviews'    => 64,
			'location'   => __( 'Dhaka', 'rentiva' ),
			'price'      => rentiva_format_price( 14 ),
			'price_unit' => __( '/ day', 'rentiva' ),
		),
		array(
			'id'         => 0,
			'title'      => __( 'Trail Master', 'rentiva' ),
			'type'       => __( 'Adventure Bike', 'rentiva' ),
			'url'        => rentiva_get_rentals_page_url(),
			'image_id'   => 0,
			'rating'     => 5.0,
			'reviews'    => 112,
			'location'   => __( 'Chittagong', 'rentiva' ),
			'price'      => rentiva_format_price( 22 ),
			'price_unit' => __( '/ day', 'rentiva' ),
		),
		array(
			'id'         => 0,
			'title'      => __( 'Weekend Pro', 'rentiva' ),
			'type'       => __( 'Hybrid Bike', 'rentiva' ),
			'url'        => rentiva_get_rentals_page_url(),
			'image_id'   => 0,
			'rating'     => 4.9,
			'reviews'    => 53,
			'location'   => __( 'Sylhet', 'rentiva' ),
			'price'      => rentiva_format_price( 16 ),
			'price_unit' => __( '/ day', 'rentiva' ),
		),
	);
}

/**
 * Render a grid of rental-card.php partials.
 *
 * @param array<int,array<string,mixed>> $items   Card-shaped arrays.
 * @param string                         $columns One of '2','3','4' (desktop column count).
 * @param bool                           $compact Use the smaller Similar-Rentals card variant.
 * @return void
 */
function rentiva_rental_grid( $items, $columns = '4', $compact = false ) {
	if ( empty( $items ) ) {
		rentiva_template_part( 'template-parts/cards/empty-state', '', array( 'context' => 'rentals' ) );
		return;
	}
	?>
	<div class="rentiva-rental-grid rentiva-rental-grid--<?php echo esc_attr( $columns ); ?>">
		<?php
		foreach ( $items as $rentiva_item ) {
			$rentiva_item['compact'] = $compact;
			rentiva_template_part( 'template-parts/cards/rental-card', '', $rentiva_item );
		}
		?>
	</div>
	<?php
}

/**
 * Fetch the items to show in a given homepage/section context, preferring
 * real data from Rentiva_Rental_Adapter and falling back to the bundled
 * demo dataset — never queries `rbfw_item` directly from a template.
 *
 * @param int    $count   Number of items.
 * @param string $context 'popular'|'newest'|'similar'.
 * @param array  $args    Extra query args passed through to the adapter.
 * @return array<int,array<string,mixed>>
 */
function rentiva_get_rental_cards( $count = 4, $context = 'popular', $args = array() ) {
	if ( class_exists( 'Rentiva_Rental_Adapter' ) ) {
		$items = Rentiva_Rental_Adapter::get_items_for_cards( $count, $context, $args );
		if ( ! empty( $items ) ) {
			return $items;
		}
	}

	return array_slice( rentiva_get_demo_rentals(), 0, $count );
}

/* -------------------------------------------------------------------------
 * Homepage categories — the 6 categories featured in the mockup design
 * (Bicycles/Scooters/Camping/Cameras/Water Sports/Outdoor Gear). Shown by
 * exact name, in this order, whenever they exist as real
 * `rbfw_item_caregory` terms — falling back to whatever other real
 * categories exist, and finally to plain labels with no term/link, so the
 * homepage and the hero search dropdown always show the same list.
 * ---------------------------------------------------------------------- */

/**
 * @return array<int,array{name:string,url:string,image_id:int}>
 */
function rentiva_get_homepage_categories() {
	$featured_names = array(
		__( 'Bicycles', 'rentiva' ),
		__( 'Scooters', 'rentiva' ),
		__( 'Camping', 'rentiva' ),
		__( 'Cameras', 'rentiva' ),
		__( 'Water Sports', 'rentiva' ),
		__( 'Outdoor Gear', 'rentiva' ),
	);

	$cards = array();

	if ( rentiva_has_booking_plugin() ) {
		foreach ( $featured_names as $name ) {
			$term = get_term_by( 'name', $name, 'rbfw_item_caregory' );
			if ( $term ) {
				$cards[] = array(
					'name'     => $term->name,
					'slug'     => $term->slug,
					'url'      => get_term_link( $term ),
					'image_id' => (int) get_term_meta( $term->term_id, 'rentiva_category_image_id', true ),
				);
			}
		}

		// Fill any remaining slots (fewer than 6 of the featured names exist)
		// with whatever other real categories are on the site.
		if ( count( $cards ) < 6 ) {
			$used_names = wp_list_pluck( $cards, 'name' );
			$other_terms = get_terms(
				array(
					'taxonomy'   => 'rbfw_item_caregory',
					'hide_empty' => false,
				)
			);
			if ( ! is_wp_error( $other_terms ) ) {
				foreach ( $other_terms as $term ) {
					if ( count( $cards ) >= 6 ) {
						break;
					}
					if ( in_array( $term->name, $used_names, true ) ) {
						continue;
					}
					$cards[] = array(
						'name'     => $term->name,
						'slug'     => $term->slug,
						'url'      => get_term_link( $term ),
						'image_id' => (int) get_term_meta( $term->term_id, 'rentiva_category_image_id', true ),
					);
				}
			}
		}
	}

	if ( empty( $cards ) ) {
		foreach ( $featured_names as $name ) {
			$cards[] = array(
				'name'     => $name,
				'slug'     => sanitize_title( $name ),
				'url'      => rentiva_get_rentals_page_url(),
				'image_id' => 0,
			);
		}
	}

	return $cards;
}

/* -------------------------------------------------------------------------
 * Reviews (native WP comments on rbfw_item, enriched with a theme-owned
 * star-rating comment-meta field — see docs/booking-integration.md for why
 * this lives on the real CPT rather than the plugin's hidden shadow product).
 * ---------------------------------------------------------------------- */

/**
 * wp_list_comments() callback that renders one review card matching the
 * mockup's DetailPage review-card design (avatar, name, date, stars, text).
 *
 * @param WP_Comment $comment Comment object.
 * @param array      $args    wp_list_comments() args.
 * @param int        $depth   Nesting depth (reviews are never threaded).
 * @return void
 */
function rentiva_review_comment( $comment, $args, $depth ) {
	$rating = (int) get_comment_meta( $comment->comment_ID, 'rentiva_rating', true );
	?>
	<li <?php comment_class( 'rentiva-review' ); ?> id="comment-<?php comment_ID(); ?>">
		<div class="rentiva-review__avatar">
			<?php echo get_avatar( $comment, 48 ); ?>
		</div>
		<div class="rentiva-review__body">
			<div class="rentiva-review__head">
				<span class="rentiva-review__name"><?php echo esc_html( get_comment_author( $comment ) ); ?></span>
				<span class="rentiva-review__date"><?php echo esc_html( get_comment_date( '', $comment ) ); ?></span>
			</div>
			<?php if ( $rating > 0 ) : ?>
				<?php rentiva_the_star_rating( $rating ); ?>
			<?php endif; ?>
			<div class="rentiva-review__text">
				<?php comment_text( $comment ); ?>
			</div>
		</div>
	</li>
	<?php
}

/**
 * Render a static (non-interactive) star-rating display, e.g. "4.5 out of
 * 5" as five inline SVG stars — used by review cards and rental cards alike.
 *
 * @param float $rating 0–5.
 * @return void
 */
function rentiva_the_star_rating( $rating ) {
	$rating = max( 0, min( 5, (float) $rating ) );
	echo '<span class="rentiva-rating__stars" aria-hidden="true">';
	for ( $star = 1; $star <= 5; $star++ ) {
		$filled = $star <= round( $rating );
		printf(
			'<svg width="12" height="12" viewBox="0 0 12 12" fill="%s"><path d="M6 1l1.39 2.82L10.5 4.27l-2.25 2.19.53 3.09L6 8.02 3.22 9.55l.53-3.09L1.5 4.27l3.11-.45L6 1z"/></svg>',
			$filled ? 'currentColor' : '#E8E4DC' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static hex value, not user input.
		);
	}
	echo '</span>';
	printf(
		'<span class="screen-reader-text">%s</span>',
		esc_html(
			sprintf(
				/* translators: %s: rating out of 5 */
				__( 'Rated %s out of 5', 'rentiva' ),
				$rating
			)
		)
	);
}

/**
 * Add a required 1–5 star-rating field to the review form on `rbfw_item`
 * posts only — ordinary blog-post comments are unaffected.
 *
 * @param array $fields comment_form() default fields.
 * @return array
 */
function rentiva_comment_form_rating_field( $fields ) {
	if ( ! is_singular( 'rbfw_item' ) ) {
		return $fields;
	}

	$rating_field = '<p class="comment-form-rating rentiva-field">' .
		'<label for="rentiva_rating" class="rentiva-field__label">' . esc_html__( 'Your rating', 'rentiva' ) . '</label>' .
		'<select name="rentiva_rating" id="rentiva_rating" required>' .
		'<option value="">' . esc_html__( 'Select a rating', 'rentiva' ) . '</option>';

	for ( $star = 5; $star >= 1; $star-- ) {
		$rating_field .= sprintf( '<option value="%1$d">%1$d</option>', $star );
	}

	$rating_field .= '</select></p>';

	return array( 'rentiva_rating' => $rating_field ) + $fields;
}
add_filter( 'comment_form_fields', 'rentiva_comment_form_rating_field' );

/**
 * Persist the submitted star rating as comment meta.
 *
 * @param int $comment_id Newly inserted comment ID.
 * @return void
 */
function rentiva_save_comment_rating( $comment_id ) {
	if ( ! isset( $_POST['rentiva_rating'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- comment_post already verifies the comment nonce/flood checks before this hook fires.
		return;
	}

	$rating = absint( wp_unslash( $_POST['rentiva_rating'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	if ( $rating >= 1 && $rating <= 5 ) {
		update_comment_meta( $comment_id, 'rentiva_rating', $rating );
	}
}
add_action( 'comment_post', 'rentiva_save_comment_rating' );

/* -------------------------------------------------------------------------
 * Footer
 * ---------------------------------------------------------------------- */

/**
 * Default footer link columns shown when the matching theme location
 * ("footer-explore"/"footer-company"/"footer-support") has no menu assigned —
 * keeps a fresh install matching the mockup's EXPLORE / COMPANY / SUPPORT
 * columns out of the box.
 *
 * @param string $location Theme location slug.
 * @return array<int,array{label:string,url:string}>
 */
function rentiva_default_footer_nav_items( $location ) {
	switch ( $location ) {
		case 'footer-explore':
			$categories = rentiva_has_booking_plugin() ? get_terms(
				array(
					'taxonomy'   => 'rbfw_item_caregory',
					'number'     => 4,
					'hide_empty' => false,
				)
			) : array();

			if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
				return array_map(
					static function ( $term ) {
						return array(
							'label' => $term->name,
							'url'   => get_term_link( $term ),
						);
					},
					$categories
				);
			}

			return array(
				array( 'label' => __( 'Bikes', 'rentiva' ), 'url' => rentiva_get_rentals_page_url() ),
				array( 'label' => __( 'Scooters', 'rentiva' ), 'url' => rentiva_get_rentals_page_url() ),
				array( 'label' => __( 'Camping', 'rentiva' ), 'url' => rentiva_get_rentals_page_url() ),
				array( 'label' => __( 'Sports Equipment', 'rentiva' ), 'url' => rentiva_get_rentals_page_url() ),
			);

		case 'footer-company':
			return array(
				array( 'label' => __( 'About', 'rentiva' ), 'url' => home_url( '/about' ) ),
				array( 'label' => __( 'How It Works', 'rentiva' ), 'url' => home_url( '/#how-it-works' ) ),
				array( 'label' => __( 'Contact', 'rentiva' ), 'url' => home_url( '/contact' ) ),
			);

		case 'footer-support':
			return array(
				array( 'label' => __( 'FAQ', 'rentiva' ), 'url' => home_url( '/faq' ) ),
				array( 'label' => __( 'Terms', 'rentiva' ), 'url' => home_url( '/terms' ) ),
				array( 'label' => __( 'Privacy', 'rentiva' ), 'url' => home_url( '/privacy' ) ),
			);
	}

	return array();
}

/**
 * Render one footer navigation column, falling back to the defaults above.
 *
 * @param string $location Theme location slug.
 * @return void
 */
function rentiva_the_footer_nav( $location ) {
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'items_wrap'     => '<ul class="rentiva-footer__links">%3$s</ul>',
				'depth'          => 1,
			)
		);
		return;
	}

	echo '<ul class="rentiva-footer__links">';
	foreach ( rentiva_default_footer_nav_items( $location ) as $item ) {
		printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $item['url'] ), esc_html( $item['label'] ) );
	}
	echo '</ul>';
}

/**
 * Social links configured in Rentiva → Theme Settings, falling back to
 * placeholder "#" links (matching the mockup) so the icons still render on a
 * fresh install.
 *
 * @return array<string,string> Platform slug => URL.
 */
function rentiva_get_social_links() {
	$defaults = array(
		'twitter'   => '',
		'instagram' => '',
		'facebook'  => '',
	);

	$configured = rentiva_get_setting( 'social_links', array() );
	if ( ! is_array( $configured ) ) {
		$configured = array();
	}

	$links = array_merge( $defaults, $configured );

	foreach ( $links as $platform => $url ) {
		$links[ $platform ] = $url ? esc_url( $url ) : '#';
	}

	return $links;
}

/**
 * URL for the header/CTA "List Your Item" action. Rentiva's plugin
 * (Booking and Rental Manager for WooCommerce) is a single-catalog-owner
 * rental engine, not a multi-vendor marketplace — so this is a marketing
 * CTA, not a real frontend submission form. Site owners point it at
 * whatever page/contact-form they use to onboard new listings via
 * Rentiva → Theme Settings; it falls back to the site's contact page (if
 * one exists by that slug) and then to the homepage final-CTA anchor.
 *
 * @return string
 */
function rentiva_get_list_item_url() {
	$configured = rentiva_get_setting( 'list_item_url', '' );
	if ( $configured ) {
		return esc_url_raw( $configured );
	}

	$contact_page = get_page_by_path( 'contact' );
	if ( $contact_page ) {
		return get_permalink( $contact_page );
	}

	return home_url( '/#final-cta' );
}
