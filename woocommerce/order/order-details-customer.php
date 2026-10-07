<?php
/**
 * Order Customer Details — Rentiva override.
 *
 * Based on WooCommerce core's order/order-details-customer.php (v8.7.0): same
 * data/hooks, reskinned as a "Customer Information" card instead of the plain
 * <address> block(s).
 */

defined( 'ABSPATH' ) || exit;

$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
?>
<section class="woocommerce-customer-details">

	<?php if ( $show_shipping ) : ?>
	<div class="rbfw-or-grid">
	<?php endif; ?>

		<div class="rbfw-or-card">
			<div class="rbfw-or-card-label"><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></div>
			<address>
				<?php echo wp_kses_post( $order->get_formatted_billing_address( esc_html__( 'N/A', 'woocommerce' ) ) ); ?>

				<?php if ( $order->get_billing_phone() ) : ?>
					<p class="woocommerce-customer-details--phone"><?php echo esc_html( $order->get_billing_phone() ); ?></p>
				<?php endif; ?>

				<?php if ( $order->get_billing_email() ) : ?>
					<p class="woocommerce-customer-details--email"><?php echo esc_html( $order->get_billing_email() ); ?></p>
				<?php endif; ?>

				<?php do_action( 'woocommerce_order_details_after_customer_address', 'billing', $order ); ?>
			</address>
		</div>

	<?php if ( $show_shipping ) : ?>

		<div class="rbfw-or-card">
			<div class="rbfw-or-card-label"><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></div>
			<address>
				<?php echo wp_kses_post( $order->get_formatted_shipping_address( esc_html__( 'N/A', 'woocommerce' ) ) ); ?>

				<?php if ( $order->get_shipping_phone() ) : ?>
					<p class="woocommerce-customer-details--phone"><?php echo esc_html( $order->get_shipping_phone() ); ?></p>
				<?php endif; ?>

				<?php do_action( 'woocommerce_order_details_after_customer_address', 'shipping', $order ); ?>
			</address>
		</div>

	</div><!-- /.rbfw-or-grid -->

	<?php endif; ?>

	<?php do_action( 'woocommerce_order_details_after_customer_details', $order ); ?>

</section>
