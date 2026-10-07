<?php
/**
 * "Order received" message — Rentiva override.
 *
 * Based on WooCommerce core's checkout/order-received.php (v8.8.0): same
 * `woocommerce_thankyou_order_received_text` filter and `$order` var, just
 * reskinned into the card/banner design used across the booking flow instead
 * of WooCommerce's plain notice paragraph.
 *
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;

$message = apply_filters(
	'woocommerce_thankyou_order_received_text',
	esc_html( __( 'Thank you. Your order has been received.', 'woocommerce' ) ),
	$order
);
?>
<div class="rbfw-or-banner">
	<div class="rbfw-or-banner-icon">
		<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
	</div>
	<h1 class="rbfw-or-banner-title"><?php echo wp_kses_post( $message ); ?></h1>
	<?php if ( $order ) : ?>
	<div class="rbfw-or-banner-chip">
		<?php esc_html_e( 'Order number', 'woocommerce' ); ?> #<?php echo esc_html( $order->get_order_number() ); ?>
	</div>
	<?php endif; ?>
</div>
