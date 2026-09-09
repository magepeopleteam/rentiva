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

$rentiva_booking_price        = Rentiva_Rental_Adapter::get_display_price( $rentiva_item_id );
$rentiva_booking_availability = Rentiva_Rental_Adapter::get_availability_status( $rentiva_item_id );

$rentiva_availability_labels = array(
	'in-stock'  => __( 'Available', 'rentiva' ),
	'low-stock' => __( 'Only a few left', 'rentiva' ),
	'sold-out'  => __( 'Sold out', 'rentiva' ),
);
?>
<div class="rentiva-booking-card" id="rentiva-booking-card">
	<div class="rentiva-card rentiva-booking-card__inner">
		<div class="rentiva-booking-card__head">
			<div class="rentiva-booking-card__head-text">
				<span class="rentiva-booking-card__eyebrow"><?php esc_html_e( 'Rent this item', 'rentiva' ); ?></span>
				<?php if ( ! empty( $rentiva_booking_price['formatted'] ) ) : ?>
					<p class="rentiva-booking-card__price">
						<span class="rentiva-booking-card__price-amount"><?php echo wp_kses_post( $rentiva_booking_price['formatted'] ); ?></span>
						<?php if ( ! empty( $rentiva_booking_price['unit'] ) ) : ?>
							<span class="rentiva-booking-card__price-unit"><?php echo esc_html( $rentiva_booking_price['unit'] ); ?></span>
						<?php endif; ?>
					</p>
				<?php endif; ?>
			</div>
			<?php if ( isset( $rentiva_availability_labels[ $rentiva_booking_availability ] ) ) : ?>
				<span class="rentiva-booking-card__badge rentiva-booking-card__badge--<?php echo esc_attr( $rentiva_booking_availability ); ?>">
					<span class="rentiva-booking-card__badge-dot"></span>
					<?php echo esc_html( $rentiva_availability_labels[ $rentiva_booking_availability ] ); ?>
				</span>
			<?php endif; ?>
		</div>

		<div class="rentiva-booking-card__form">
			<?php Rentiva_Rental_Adapter::render_booking_form( $rentiva_item_id ); ?>
		</div>

		<div class="rentiva-booking-card__trust">
			<div class="rentiva-booking-card__trust-item">
				<span class="rentiva-booking-card__trust-icon"><?php echo rentiva_get_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php esc_html_e( 'Secure booking', 'rentiva' ); ?></span>
			</div>
			<div class="rentiva-booking-card__trust-item">
				<span class="rentiva-booking-card__trust-icon"><?php echo rentiva_get_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span><?php esc_html_e( 'Free cancellation', 'rentiva' ); ?></span>
			</div>
		</div>
	</div>
</div>
