<?php
/**
 * Pickup Location card — map placeholder, address, hours, directions link.
 *
 * RBFW stores pickup points as a list the renter picks from at booking time
 * (`rbfw_pickup_data`), not a single fixed address — so this display card
 * shows the item's primary Location taxonomy term as the address, and a
 * site-wide default hours string (Rentiva → Theme Settings), rather than
 * inventing a new per-item address field.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_item_id = isset( $args['item_id'] ) ? (int) $args['item_id'] : get_the_ID();
$rentiva_location = Rentiva_Rental_Adapter::get_location_label( $rentiva_item_id );

if ( ! $rentiva_location ) {
	return;
}

$rentiva_hours = rentiva_get_setting( 'pickup_hours', __( 'Available daily 8:00 AM – 8:00 PM', 'rentiva' ) );
$rentiva_maps_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $rentiva_location );
?>
<div class="rentiva-item-section rentiva-item-pickup">
	<h2 class="rentiva-h4"><?php esc_html_e( 'Pickup Location', 'rentiva' ); ?></h2>
	<div class="rentiva-card rentiva-item-pickup__card">
		<div class="rentiva-item-pickup__map" aria-hidden="true">
			<?php echo rentiva_get_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<div class="rentiva-item-pickup__footer">
			<div>
				<p class="rentiva-item-pickup__address"><?php echo esc_html( $rentiva_location ); ?></p>
				<p class="rentiva-item-pickup__hours"><?php echo esc_html( $rentiva_hours ); ?></p>
			</div>
			<a href="<?php echo esc_url( $rentiva_maps_url ); ?>" class="btn btn--primary" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Get Directions', 'rentiva' ); ?>
			</a>
		</div>
	</div>
</div>
