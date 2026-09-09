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

// $args['stats'] — a full replacement list when rendering as the Rentiva:
// Trust Strip Elementor widget's repeater (its own add/remove/reorder rows
// wholly define the set; only an entirely empty repeater falls back to
// Theme Settings) — see Rentiva_Elementor_Widget_Trust_Strip::render().
$rentiva_stats = ( ! empty( $args['stats'] ) && is_array( $args['stats'] ) )
	? $args['stats']
	: rentiva_get_default_trust_stats();
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
