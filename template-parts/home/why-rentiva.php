<?php
/**
 * "Why Rentiva" — 2-col image + feature checklist.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_image_id = ! empty( $args['image_id'] ) ? (int) $args['image_id'] : (int) rentiva_get_setting( 'why_image_id', 0 );

// $args['features'] — a full replacement list when rendering as the
// Rentiva: Why Rentiva Elementor widget's repeater (its own add/remove/
// reorder rows wholly define the set; only an entirely empty repeater
// falls back to the built-in 4 features) — see
// Rentiva_Elementor_Widget_Why_Rentiva::render().
$rentiva_features = ( ! empty( $args['features'] ) && is_array( $args['features'] ) )
	? $args['features']
	: rentiva_get_default_why_rentiva_features();

$rentiva_eyebrow   = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : __( 'WHY RENTIVA', 'rentiva' );
$rentiva_heading   = ! empty( $args['heading'] ) ? $args['heading'] : __( 'Everything you need for a better rental.', 'rentiva' );
$rentiva_link_text = ! empty( $args['link_text'] ) ? $args['link_text'] : __( 'Learn More', 'rentiva' );
$rentiva_link_url  = ! empty( $args['link_url'] ) ? $args['link_url'] : rentiva_get_rentals_page_url();
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
			<p class="rentiva-eyebrow"><?php echo esc_html( $rentiva_eyebrow ); ?></p>
			<h2 class="rentiva-h3 rentiva-why-rentiva__title">
				<?php echo esc_html( $rentiva_heading ); ?>
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

			<a href="<?php echo esc_url( $rentiva_link_url ); ?>" class="rentiva-link" style="margin-top:2.5rem;">
				<?php echo esc_html( $rentiva_link_text ); ?>
				<?php echo rentiva_get_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>
	</div>
</section>
