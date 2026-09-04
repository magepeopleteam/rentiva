<?php
/**
 * Large full-width promo banner — "Weekend Special" CTA.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_image_id = (int) rentiva_get_setting( 'promo_image_id', 0 );
$rentiva_badge     = rentiva_get_setting( 'promo_badge', __( 'WEEKEND SPECIAL · UP TO 20% OFF', 'rentiva' ) );
$rentiva_title     = rentiva_get_setting( 'promo_title', __( 'Your next adventure starts here.', 'rentiva' ) );
$rentiva_text      = rentiva_get_setting( 'promo_text', __( 'Discover premium equipment from trusted local owners.', 'rentiva' ) );
?>
<section class="rentiva-promo-banner">
	<div class="rentiva-promo-banner__media">
		<?php if ( $rentiva_image_id ) : ?>
			<?php echo wp_get_attachment_image( $rentiva_image_id, 'rentiva-promo', false, array( 'class' => 'rentiva-promo-banner__image', 'alt' => '' ) ); ?>
		<?php else : ?>
			<div class="rentiva-promo-banner__image rentiva-promo-banner__image--placeholder" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="rentiva-promo-banner__gradient" aria-hidden="true"></div>
	</div>

	<div class="rentiva-container rentiva-promo-banner__inner">
		<div class="rentiva-promo-banner__content">
			<span class="rentiva-badge rentiva-promo-banner__badge"><?php echo esc_html( $rentiva_badge ); ?></span>
			<h2 class="rentiva-promo-banner__title"><?php echo wp_kses_post( nl2br( esc_html( $rentiva_title ) ) ); ?></h2>
			<p class="rentiva-promo-banner__text"><?php echo esc_html( $rentiva_text ); ?></p>
			<a href="<?php echo esc_url( rentiva_get_rentals_page_url() ); ?>" class="btn btn--white btn--lg">
				<?php esc_html_e( 'Explore Rentals', 'rentiva' ); ?>
			</a>
		</div>
	</div>
</section>
