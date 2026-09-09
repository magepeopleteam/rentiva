<?php
/**
 * "Explore What You Need" — 6-card category grid.
 *
 * Shows the mockup's 6 featured categories (Bicycles/Scooters/Camping/
 * Cameras/Water Sports/Outdoor Gear) by name when they exist as real
 * `rbfw_item_caregory` terms — see rentiva_get_homepage_categories() in
 * inc/template-functions.php, which the hero search dropdown also uses so
 * both stay in sync.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// $args['cards'] — a full replacement list when rendering as the Rentiva:
// Categories Elementor widget's category-picker repeater (its own add/
// remove/reorder rows wholly define the set; only an entirely empty picker
// falls back to the built-in 6 featured categories) — see
// Rentiva_Elementor_Widget_Categories::render().
$rentiva_cards = ( ! empty( $args['cards'] ) && is_array( $args['cards'] ) )
	? $args['cards']
	: rentiva_get_homepage_categories();

// $args overrides when rendering as the Rentiva: Categories Elementor widget.
$rentiva_heading   = ! empty( $args['heading'] ) ? $args['heading'] : __( 'Explore What You Need', 'rentiva' );
$rentiva_lead      = ! empty( $args['lead'] ) ? $args['lead'] : __( 'Find the right equipment for your next adventure.', 'rentiva' );
$rentiva_link_text = ! empty( $args['link_text'] ) ? $args['link_text'] : __( 'View all categories', 'rentiva' );
$rentiva_link_url  = ! empty( $args['link_url'] ) ? $args['link_url'] : rentiva_get_rentals_page_url();
?>
<section class="rentiva-section--lg rentiva-categories" id="categories">
	<div class="rentiva-container">
		<div class="rentiva-section-head">
			<div class="rentiva-section-head__title">
				<h2 class="rentiva-h2"><?php echo esc_html( $rentiva_heading ); ?></h2>
				<p class="rentiva-lead"><?php echo esc_html( $rentiva_lead ); ?></p>
			</div>
			<a href="<?php echo esc_url( $rentiva_link_url ); ?>" class="rentiva-link">
				<?php echo esc_html( $rentiva_link_text ); ?>
				<?php echo rentiva_get_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>

		<div class="rentiva-categories__grid">
			<?php foreach ( $rentiva_cards as $rentiva_card ) : ?>
				<?php rentiva_template_part( 'template-parts/cards/category-card', '', $rentiva_card ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
