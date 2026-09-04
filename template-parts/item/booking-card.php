<?php
/**
 * Sticky booking card. The card chrome (title, trust bullets) is the
 * theme's own; the interactive form inside it — date pickers, quantity,
 * AJAX price calc, stock checks, cart submission — is the plugin's real,
 * unmodified form, embedded via Rentiva_Rental_Adapter::render_booking_form().
 * booking.css re-skins that plugin markup to match the mockup; it never
 * duplicates its logic. See docs/booking-integration.md.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_item_id = isset( $args['item_id'] ) ? (int) $args['item_id'] : get_the_ID();
?>
<div class="rentiva-booking-card" id="rentiva-booking-card">
	<div class="rentiva-card rentiva-booking-card__inner">
		<h3 class="rentiva-h4 rentiva-booking-card__title"><?php esc_html_e( 'Rent this item', 'rentiva' ); ?></h3>

		<div class="rentiva-booking-card__form">
			<?php Rentiva_Rental_Adapter::render_booking_form( $rentiva_item_id ); ?>
		</div>

		<div class="rentiva-booking-card__trust">
			<div class="rentiva-booking-card__trust-item">
				<?php echo rentiva_get_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( 'Secure booking', 'rentiva' ); ?></span>
			</div>
			<div class="rentiva-booking-card__trust-item">
				<?php echo rentiva_get_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( 'Free cancellation', 'rentiva' ); ?></span>
			</div>
		</div>
	</div>
</div>
