<?php
/**
 * Full-width dark testimonial quote.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_quote  = rentiva_get_setting( 'testimonial_quote', __( 'The easiest rental experience I\'ve ever had. The bike was perfect and the entire process took less than two minutes.', 'rentiva' ) );
$rentiva_name   = rentiva_get_setting( 'testimonial_name', __( 'Daniel Morgan', 'rentiva' ) );
$rentiva_role   = rentiva_get_setting( 'testimonial_role', __( 'Weekend Traveler', 'rentiva' ) );
$rentiva_avatar = (int) rentiva_get_setting( 'testimonial_avatar_id', 0 );
?>
<section class="rentiva-section rentiva-section--dark rentiva-testimonial">
	<div class="rentiva-container rentiva-container--narrow rentiva-testimonial__inner">
		<div class="rentiva-testimonial__stars">
			<?php rentiva_the_star_rating( 5 ); ?>
		</div>
		<blockquote class="rentiva-testimonial__quote">
			&ldquo;<?php echo esc_html( $rentiva_quote ); ?>&rdquo;
		</blockquote>
		<div class="rentiva-testimonial__author">
			<span class="rentiva-testimonial__avatar">
				<?php if ( $rentiva_avatar ) : ?>
					<?php echo wp_get_attachment_image( $rentiva_avatar, 'rentiva-square', false, array( 'alt' => $rentiva_name ) ); ?>
				<?php endif; ?>
			</span>
			<span class="rentiva-testimonial__meta">
				<strong><?php echo esc_html( $rentiva_name ); ?></strong>
				<span><?php echo esc_html( $rentiva_role ); ?></span>
			</span>
		</div>
	</div>
</section>
