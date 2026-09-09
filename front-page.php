<?php
/**
 * The homepage template — always wins for "/" (see docs/architecture.md).
 *
 * Renders the Elementor-built "Homepage" page's own content when one is set
 * as the static front page (rentiva_homepage_uses_custom_builder()) — which
 * Rentiva Setup creates automatically on theme activation, since Elementor
 * is a hard theme dependency; otherwise falls back to the theme's built-in,
 * filterable section layout that matches mockup/src/pages/HomePage.tsx
 * exactly.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( rentiva_homepage_uses_custom_builder() ) {
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
} else {
	/**
	 * Filters the ordered list of homepage section slugs. Each slug maps to
	 * template-parts/home/{slug}.php. Add/remove/reorder sections here rather
	 * than editing front-page.php directly.
	 *
	 * @param string[] $sections Section slugs in render order.
	 */
	$rentiva_home_sections = apply_filters(
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

	foreach ( $rentiva_home_sections as $rentiva_section ) {
		rentiva_template_part( 'template-parts/home/' . $rentiva_section );
	}
}

get_footer();
