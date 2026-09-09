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
 * The Trust Strip's 4 stats — whatever is currently live (the Rentiva →
 * Theme Settings value if set, else the built-in default). Single source
 * of truth for template-parts/home/trust-strip.php AND
 * Rentiva_Elementor_Widget_Trust_Strip's Stats repeater default, so opening
 * that widget in Elementor shows the 4 rows actually on the page — ready to
 * tweak — instead of an empty repeater with nothing to click.
 *
 * @return array<int,array{value:string,label:string}>
 */
function rentiva_get_default_trust_stats() {
	$stats = rentiva_get_setting( 'trust_stats', array() );
	if ( ! empty( $stats ) && is_array( $stats ) ) {
		return $stats;
	}

	return array(
		array( 'value' => __( '10,000+', 'rentiva' ), 'label' => __( 'rentals completed', 'rentiva' ) ),
		array( 'value' => __( '4.9 / 5', 'rentiva' ), 'label' => __( 'average rating', 'rentiva' ) ),
		array( 'value' => __( '100%', 'rentiva' ), 'label' => __( 'verified equipment', 'rentiva' ) ),
		array( 'value' => __( 'Secure', 'rentiva' ), 'label' => __( 'booking guaranteed', 'rentiva' ) ),
	);
}

/**
 * The "How It Works" section's 3 built-in steps. Single source of truth for
 * template-parts/home/how-it-works.php AND
 * Rentiva_Elementor_Widget_How_It_Works's Steps repeater default (see
 * rentiva_get_default_trust_stats() for why). No Theme Settings field
 * exists for these, so this is always the hardcoded default.
 *
 * @return array<int,array{title:string,desc:string}>
 */
function rentiva_get_default_how_it_works_steps() {
	return array(
		array(
			'title' => __( 'Find', 'rentiva' ),
			'desc'  => __( 'Discover the perfect equipment near you.', 'rentiva' ),
		),
		array(
			'title' => __( 'Book', 'rentiva' ),
			'desc'  => __( 'Choose your dates and reserve in seconds.', 'rentiva' ),
		),
		array(
			'title' => __( 'Enjoy', 'rentiva' ),
			'desc'  => __( 'Pick it up and start your adventure.', 'rentiva' ),
		),
	);
}

/**
 * The "Why Rentiva" section's 4 built-in features. Single source of truth
 * for template-parts/home/why-rentiva.php AND
 * Rentiva_Elementor_Widget_Why_Rentiva's Features repeater default (see
 * rentiva_get_default_trust_stats() for why). No Theme Settings field
 * exists for these, so this is always the hardcoded default.
 *
 * @return array<int,array{title:string,desc:string}>
 */
function rentiva_get_default_why_rentiva_features() {
	return array(
		array(
			'title' => __( 'Verified Equipment', 'rentiva' ),
			'desc'  => __( 'Every item is reviewed and quality checked.', 'rentiva' ),
		),
		array(
			'title' => __( 'Flexible Booking', 'rentiva' ),
			'desc'  => __( 'Choose the dates and rental period that work for you.', 'rentiva' ),
		),
		array(
			'title' => __( 'Transparent Pricing', 'rentiva' ),
			'desc'  => __( 'No confusing fees or hidden surprises.', 'rentiva' ),
		),
		array(
			'title' => __( 'Secure Payments', 'rentiva' ),
			'desc'  => __( 'Simple and secure checkout every time.', 'rentiva' ),
		),
	);
}

/**
 * `term_id => term_name` options for the Rentiva: Categories Elementor
 * widget's category-picker repeater (Controls_Manager::SELECT2).
 *
 * @return array<int,string>
 */
function rentiva_get_category_picker_options() {
	if ( ! rentiva_has_booking_plugin() ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'rbfw_item_caregory',
			'hide_empty' => false,
			'orderby'    => 'name',
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	$options = array();
	foreach ( $terms as $term ) {
		$options[ $term->term_id ] = $term->name;
	}

	return $options;
}

/**
 * Default rows for that same repeater — the 6 categories currently shown
 * (rentiva_get_homepage_categories()), as {term_id} rows, so opening the
 * widget shows the current picks pre-filled and ready to edit/reorder
 * instead of an empty repeater with nothing to click.
 *
 * @return array<int,array{term_id:int}>
 */
function rentiva_get_default_category_repeater_rows() {
	$rows = array();
	foreach ( rentiva_get_homepage_categories() as $card ) {
		if ( ! empty( $card['term_id'] ) ) {
			$rows[] = array( 'term_id' => $card['term_id'] );
		}
	}
	return $rows;
}

/**
 * `post_id => post_title` options for the Rentiva: Popular Rentals
 * Elementor widget's item-picker repeater (Controls_Manager::SELECT2).
 * Every published `rbfw_item`, not just the "popular" subset, so an admin
 * can feature any real item here regardless of comment/booking count.
 *
 * @return array<int,string>
 */
function rentiva_get_rental_item_picker_options() {
	if ( ! rentiva_has_booking_plugin() ) {
		return array();
	}

	$posts = get_posts(
		array(
			'post_type'      => 'rbfw_item',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
		)
	);

	$options = array();
	foreach ( $posts as $post_id ) {
		$options[ $post_id ] = get_the_title( $post_id );
	}

	return $options;
}

/**
 * Default rows for that same repeater — the items currently shown
 * (rentiva_get_rental_cards( 4, 'popular' )), as {item_id} rows, so opening
 * the widget shows the current picks pre-filled and ready to edit/reorder
 * instead of an empty repeater with nothing to click.
 *
 * @return array<int,array{item_id:int}>
 */
function rentiva_get_default_rental_item_repeater_rows() {
	$rows = array();
	foreach ( rentiva_get_rental_cards( 4, 'popular' ) as $card ) {
		if ( ! empty( $card['id'] ) ) {
			$rows[] = array( 'item_id' => $card['id'] );
		}
	}
	return $rows;
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
 * Whether the homepage should be handed over to the static front page's own
 * content (the Elementor-built "Homepage" page Rentiva Setup creates on
 * activation — see rentiva_setup_homepage_page()) instead of the theme's
 * built-in front-page.php section layout.
 *
 * The built-in layout only remains as a fallback for sites where that page
 * doesn't exist or has no content yet (e.g. Elementor wasn't active at
 * activation time, or an admin reset Settings → Reading back to "Your
 * latest posts").
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

	$has_content = '' !== trim( (string) $front_page->post_content );

	// Require actual saved elements, not just the `builder` edit-mode flag:
	// Elementor sets that flag the moment its editor is opened for a page,
	// even before anything is saved (and rentiva_setup_homepage_page() only
	// seeds a page while `_elementor_data` is still empty) — without this
	// check, a page that's in builder mode but genuinely has no elements
	// yet would render as a blank homepage instead of falling back safely.
	$has_elementor = 'builder' === get_post_meta( $front_id, '_elementor_edit_mode', true )
		&& '' !== (string) get_post_meta( $front_id, '_elementor_data', true );

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

/**
 * Find the "Homepage" page Rentiva Setup creates, if one already exists.
 *
 * Matched by title rather than a stored option, so the check stays correct
 * even if an admin recreated the page by hand.
 *
 * @return int Page ID, or 0 if no such page exists yet.
 */
function rentiva_get_homepage_page_id() {
	$existing = new WP_Query(
		array(
			'post_type'              => 'page',
			'post_status'            => 'any',
			'title'                  => 'Homepage',
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
 * Create (if missing) a "Homepage" page — pre-built with Elementor, one
 * section per built-in homepage widget (see inc/integrations/elementor.php)
 * so it looks identical to the built-in layout but is fully editable in
 * Elementor from the start — and set it as the static front page. Safe to
 * call more than once: seeding only ever fills in `_elementor_data` while
 * it's still empty (e.g. the page was created before Elementor was fully
 * active, so seeding was skipped, or the editor was opened but never
 * saved) — once an admin has actually saved real content, this never
 * touches the page again.
 *
 * @return int The homepage page ID, or 0 on failure.
 */
function rentiva_setup_homepage_page() {
	$page_id = rentiva_get_homepage_page_id();

	if ( ! $page_id ) {
		$page_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Homepage',
				'post_status'  => 'publish',
				'post_content' => '',
			)
		);

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			return 0;
		}
	}

	if ( rentiva_has_elementor() && '' === (string) get_post_meta( $page_id, '_elementor_data', true ) ) {
		rentiva_seed_homepage_elementor_data( $page_id );
	}

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $page_id );

	return (int) $page_id;
}

/**
 * Pre-build a page's `_elementor_data` with one section per built-in
 * homepage widget, in the same order as the fallback layout's
 * `rentiva_home_sections` filter — every `rentiva-{slug}` widget type here
 * matches a class registered in inc/integrations/elementor.php exactly.
 *
 * @param int $page_id
 * @return void
 */
function rentiva_seed_homepage_elementor_data( $page_id ) {
	$section_slugs = apply_filters(
		'rentiva_home_sections',
		array(
			'hero',
			'trust-strip',
			'categories',
			'popular-rentals',
			'promo-banner',
			'how-it-works',
			'why-rentiva',
			'testimonial',
			'final-cta',
		)
	);

	// Every section is seeded Full Width with no column gap: each widget
	// renders its own full-bleed <section> (its own max-width/padding is
	// already baked into template-parts/home/*.php's CSS, matching the
	// approved mockup/rentiva.html design exactly), so Elementor's own
	// defaults — a 1140px "Boxed" content width and a 10px column-gap
	// padding around every widget — must be turned off here. Otherwise
	// every section would render visibly narrower/inset the moment an
	// admin opens this page, and they'd have to know to fix each
	// section's Content Width/Gap themselves to match the design.
	$section_settings = array(
		'layout' => 'full_width',
		'gap'    => 'no',
		'margin' => array(
			'unit'     => 'px',
			'top'      => '0',
			'bottom'   => '0',
			'isLinked' => false,
		),
		'padding' => array(
			'unit'     => 'px',
			'top'      => '0',
			'right'    => '0',
			'bottom'   => '0',
			'left'     => '0',
			'isLinked' => false,
		),
	);

	$sections = array();
	foreach ( $section_slugs as $slug ) {
		$sections[] = array(
			'id'       => rentiva_generate_elementor_id(),
			'elType'   => 'section',
			'settings' => $section_settings,
			'elements' => array(
				array(
					'id'       => rentiva_generate_elementor_id(),
					'elType'   => 'column',
					'settings' => array( '_column_size' => 100 ),
					'elements' => array(
						array(
							'id'         => rentiva_generate_elementor_id(),
							'elType'     => 'widget',
							'settings'   => array(),
							'widgetType' => 'rentiva-' . $slug,
							'elements'   => array(),
						),
					),
				),
			),
		);
	}

	update_post_meta( $page_id, '_elementor_data', wp_json_encode( $sections ) );
	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $page_id, '_elementor_page_settings', array() );

	if ( defined( 'ELEMENTOR_VERSION' ) ) {
		update_post_meta( $page_id, '_elementor_version', ELEMENTOR_VERSION );
	}

	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}

/**
 * A short unique id in the format Elementor uses for its own section/
 * column/widget element nodes.
 *
 * @return string
 */
function rentiva_generate_elementor_id() {
	return substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 7 );
}
