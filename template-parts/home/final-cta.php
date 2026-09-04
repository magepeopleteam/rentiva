<?php
/**
 * Final CTA — light section with two radial-gradient accents.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="rentiva-final-cta" id="final-cta">
	<div class="rentiva-final-cta__glow" aria-hidden="true"></div>
	<div class="rentiva-container rentiva-container--narrow rentiva-final-cta__inner">
		<h2 class="rentiva-h1"><?php esc_html_e( 'Ready to explore?', 'rentiva' ); ?></h2>
		<p class="rentiva-lead"><?php esc_html_e( 'Find your perfect rental and start your next adventure.', 'rentiva' ); ?></p>
		<div class="rentiva-final-cta__actions">
			<a href="<?php echo esc_url( rentiva_get_rentals_page_url() ); ?>" class="btn btn--primary btn--lg">
				<?php esc_html_e( 'Explore Rentals', 'rentiva' ); ?>
			</a>
			<a href="<?php echo esc_url( rentiva_get_list_item_url() ); ?>" class="btn btn--outline btn--lg">
				<?php esc_html_e( 'List Your Item', 'rentiva' ); ?>
			</a>
		</div>
	</div>
</section>
