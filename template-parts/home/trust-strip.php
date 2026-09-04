<?php
/**
 * Trust strip — 4 stats between hairline borders, directly under the hero's
 * overlapping search panel.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_stats = rentiva_get_setting( 'trust_stats', array() );
if ( empty( $rentiva_stats ) || ! is_array( $rentiva_stats ) ) {
	$rentiva_stats = array(
		array( 'value' => __( '10,000+', 'rentiva' ), 'label' => __( 'rentals completed', 'rentiva' ) ),
		array( 'value' => __( '4.9 / 5', 'rentiva' ), 'label' => __( 'average rating', 'rentiva' ) ),
		array( 'value' => __( '100%', 'rentiva' ), 'label' => __( 'verified equipment', 'rentiva' ) ),
		array( 'value' => __( 'Secure', 'rentiva' ), 'label' => __( 'booking guaranteed', 'rentiva' ) ),
	);
}
?>
<section class="rentiva-trust-strip">
	<div class="rentiva-container">
		<div class="rentiva-trust-strip__row">
			<?php foreach ( $rentiva_stats as $rentiva_stat ) : ?>
				<div class="rentiva-trust-strip__item">
					<p class="rentiva-trust-strip__value"><?php echo esc_html( $rentiva_stat['value'] ); ?></p>
					<p class="rentiva-trust-strip__label"><?php echo esc_html( $rentiva_stat['label'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
