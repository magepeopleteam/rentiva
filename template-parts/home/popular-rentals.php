<?php
/**
 * "Popular Rentals" — 4-col grid of real (or demo-fallback) rental items.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// $args['items'] — a full replacement list when rendering as the Rentiva:
// Popular Rentals Elementor widget's item-picker repeater (its own add/
// remove/reorder rows wholly define the set; only an entirely empty picker
// falls back to the built-in "most popular" query) — see
// Rentiva_Elementor_Widget_Popular_Rentals::render().
$rentiva_items = ( ! empty( $args['items'] ) && is_array( $args['items'] ) )
	? $args['items']
	: rentiva_get_rental_cards( 4, 'popular' );

// $args overrides when rendering as the Rentiva: Popular Rentals Elementor widget.
$rentiva_heading   = ! empty( $args['heading'] ) ? $args['heading'] : __( 'Popular Rentals', 'rentiva' );
$rentiva_lead      = ! empty( $args['lead'] ) ? $args['lead'] : __( 'Highly rated equipment ready for your next adventure.', 'rentiva' );
$rentiva_link_text = ! empty( $args['link_text'] ) ? $args['link_text'] : __( 'View all rentals', 'rentiva' );
$rentiva_link_url  = ! empty( $args['link_url'] ) ? $args['link_url'] : rentiva_get_rentals_page_url();
?>
<section class="rentiva-section rentiva-section--flush-top rentiva-popular-rentals" id="popular-rentals">
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

		<?php rentiva_rental_grid( $rentiva_items, '4' ); ?>
	</div>
</section>
