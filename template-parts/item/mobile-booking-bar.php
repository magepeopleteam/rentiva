<?php
/**
 * Fixed mobile bottom bar — price + a "Reserve Now" link that scrolls to
 * the real booking card (the sticky sidebar is hidden on small screens
 * where the layout stacks to one column).
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_item_id = isset( $args['item_id'] ) ? (int) $args['item_id'] : get_the_ID();
$rentiva_price    = Rentiva_Rental_Adapter::get_display_price( $rentiva_item_id );
?>
<div class="rentiva-mobile-booking-bar">
	<div class="rentiva-mobile-booking-bar__price">
		<span class="rentiva-mobile-booking-bar__amount"><?php echo wp_kses_post( $rentiva_price['formatted'] ); ?></span>
		<span class="rentiva-mobile-booking-bar__unit"><?php echo esc_html( $rentiva_price['unit'] ); ?></span>
	</div>
	<a href="#rentiva-booking-card" class="btn btn--primary rentiva-mobile-booking-bar__cta">
		<?php esc_html_e( 'Reserve Now', 'rentiva' ); ?>
	</a>
</div>
