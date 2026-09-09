<?php
/**
 * "Why Rentiva" — 2-col image + feature checklist.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_image_id = (int) rentiva_get_setting( 'why_image_id', 0 );

$rentiva_features = array(
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
?>
<section class="rentiva-section--lg rentiva-section--flush-top rentiva-why-rentiva">
	<div class="rentiva-container rentiva-why-rentiva__grid">
		<div class="rentiva-why-rentiva__media">
			<?php if ( $rentiva_image_id ) : ?>
				<?php echo wp_get_attachment_image( $rentiva_image_id, 'rentiva-why', false, array( 'class' => 'rentiva-why-rentiva__image', 'alt' => '' ) ); ?>
			<?php else : ?>
				<div class="rentiva-why-rentiva__image rentiva-why-rentiva__image--placeholder" aria-hidden="true"></div>
			<?php endif; ?>
		</div>

		<div>
			<p class="rentiva-eyebrow"><?php esc_html_e( 'WHY RENTIVA', 'rentiva' ); ?></p>
			<h2 class="rentiva-h3 rentiva-why-rentiva__title">
				<?php esc_html_e( 'Everything you need for a better rental.', 'rentiva' ); ?>
			</h2>

			<ul class="rentiva-checklist">
				<?php foreach ( $rentiva_features as $rentiva_feature ) : ?>
					<li class="rentiva-checklist__item">
						<span class="rentiva-checklist__icon">
							<?php echo rentiva_get_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<span>
							<strong class="rentiva-why-rentiva__feature-title"><?php echo esc_html( $rentiva_feature['title'] ); ?></strong>
							<span class="rentiva-why-rentiva__feature-desc"><?php echo esc_html( $rentiva_feature['desc'] ); ?></span>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>

			<a href="<?php echo esc_url( rentiva_get_rentals_page_url() ); ?>" class="rentiva-link" style="margin-top:2.5rem;">
				<?php esc_html_e( 'Learn More', 'rentiva' ); ?>
				<?php echo rentiva_get_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>
	</div>
</section>
