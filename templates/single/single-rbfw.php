<?php
/**
 * Single rental-item page chrome.
 *
 * RBFW_Function::get_template_path() checks `get_stylesheet_directory() .
 * '/templates/...'` before falling back to the plugin's own bundled
 * templates, so this file is picked up automatically by RBFW's
 * `single_template` filter — no filter override needed in this theme.
 * See docs/booking-integration.md for the full mechanism.
 *
 * This file owns the page layout only. The interactive booking form itself
 * (date pickers, AJAX price calc, stock checks, cart submission) is
 * rendered by Rentiva_Rental_Adapter::render_booking_form(), which embeds
 * the plugin's own, unmodified form partial — see template-parts/item/booking-card.php.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! rentiva_use_theme_single_item_layout() && class_exists( 'RBFW_Function' ) ) {
	// Default: defer entirely to the plugin's own bundled page so each
	// item's selected template (Default/Muffin) renders as chosen, instead
	// of this theme's layout below. Opt into the theme layout via
	// Rentiva → Theme Settings → Single item layout.
	//
	// The plugin's single-rbfw.php is a self-contained `single_template`
	// entry point — it calls get_header()/the_post()/get_footer() itself —
	// so it must be included here directly, before this file's own
	// get_header() and while(have_posts()):the_post() below ever run.
	// Running both meant get_header() printed twice (duplicated header
	// markup) and the_post() was called twice against a single-post query,
	// which pushed WP_Query::next_post() past the only available post
	// (an "Undefined array key 1" notice) and left the global $post null
	// for the rest of the request — emptying titles/content site-wide.
	include RBFW_TEMPLATE_PATH . 'single/single-rbfw.php';
	return;
}

get_header();

while ( have_posts() ) :
	the_post();
	$rentiva_item_id = get_the_ID();
	?>

	<?php rentiva_template_part( 'template-parts/item/breadcrumb', '', array( 'item_id' => $rentiva_item_id ) ); ?>

	<div class="rentiva-container rentiva-single-item">
		<div class="rentiva-single-item__grid">
			<div class="rentiva-single-item__main">
				<?php rentiva_template_part( 'template-parts/item/gallery', '', array( 'item_id' => $rentiva_item_id ) ); ?>
				<?php rentiva_template_part( 'template-parts/item/info', '', array( 'item_id' => $rentiva_item_id ) ); ?>
				<?php rentiva_template_part( 'template-parts/item/specs', '', array( 'item_id' => $rentiva_item_id ) ); ?>
				<?php rentiva_template_part( 'template-parts/item/about', '', array( 'item_id' => $rentiva_item_id ) ); ?>
				<?php rentiva_template_part( 'template-parts/item/pickup-location', '', array( 'item_id' => $rentiva_item_id ) ); ?>
				<?php rentiva_template_part( 'template-parts/item/reviews', '', array( 'item_id' => $rentiva_item_id ) ); ?>
			</div>

			<div class="rentiva-single-item__aside">
				<?php rentiva_template_part( 'template-parts/item/booking-card', '', array( 'item_id' => $rentiva_item_id ) ); ?>
			</div>
		</div>
	</div>

	<?php rentiva_template_part( 'template-parts/item/mobile-booking-bar', '', array( 'item_id' => $rentiva_item_id ) ); ?>
	<?php rentiva_template_part( 'template-parts/item/similar-items', '', array( 'item_id' => $rentiva_item_id ) ); ?>

	<?php
endwhile;

get_footer();
