<?php
/**
 * "Popular Rentals" — 4-col grid of real (or demo-fallback) rental items.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_items = rentiva_get_rental_cards( 4, 'popular' );
?>
<section class="rentiva-section rentiva-section--flush-top rentiva-popular-rentals" id="popular-rentals">
	<div class="rentiva-container">
		<div class="rentiva-section-head">
			<div class="rentiva-section-head__title">
				<h2 class="rentiva-h2"><?php esc_html_e( 'Popular Rentals', 'rentiva' ); ?></h2>
				<p class="rentiva-lead"><?php esc_html_e( 'Highly rated equipment ready for your next adventure.', 'rentiva' ); ?></p>
			</div>
			<a href="<?php echo esc_url( rentiva_get_rentals_page_url() ); ?>" class="rentiva-link">
				<?php esc_html_e( 'View all rentals', 'rentiva' ); ?>
				<?php echo rentiva_get_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>

		<?php rentiva_rental_grid( $rentiva_items, '4' ); ?>
	</div>
</section>
