<?php
/**
 * Final CTA — light section with two radial-gradient accents.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// $args overrides when rendering as the Rentiva: Final CTA Elementor widget.
$rentiva_heading            = ! empty( $args['heading'] ) ? $args['heading'] : __( 'Ready to explore?', 'rentiva' );
$rentiva_lead               = ! empty( $args['lead'] ) ? $args['lead'] : __( 'Find your perfect rental and start your next adventure.', 'rentiva' );
$rentiva_cta_primary_text   = ! empty( $args['cta_primary_text'] ) ? $args['cta_primary_text'] : __( 'Explore Rentals', 'rentiva' );
$rentiva_cta_primary_url    = ! empty( $args['cta_primary_url'] ) ? $args['cta_primary_url'] : rentiva_get_rentals_page_url();
$rentiva_cta_secondary_text = ! empty( $args['cta_secondary_text'] ) ? $args['cta_secondary_text'] : __( 'List Your Item', 'rentiva' );
$rentiva_cta_secondary_url  = ! empty( $args['cta_secondary_url'] ) ? $args['cta_secondary_url'] : rentiva_get_list_item_url();
?>
<section class="rentiva-final-cta" id="final-cta">
	<div class="rentiva-final-cta__glow" aria-hidden="true"></div>
	<div class="rentiva-container rentiva-container--narrow rentiva-final-cta__inner">
		<h2 class="rentiva-h1"><?php echo esc_html( $rentiva_heading ); ?></h2>
		<p class="rentiva-lead"><?php echo esc_html( $rentiva_lead ); ?></p>
		<div class="rentiva-final-cta__actions">
			<a href="<?php echo esc_url( $rentiva_cta_primary_url ); ?>" class="btn btn--primary btn--lg">
				<?php echo esc_html( $rentiva_cta_primary_text ); ?>
			</a>
			<a href="<?php echo esc_url( $rentiva_cta_secondary_url ); ?>" class="btn btn--outline btn--lg">
				<?php echo esc_html( $rentiva_cta_secondary_text ); ?>
			</a>
		</div>
	</div>
</section>
