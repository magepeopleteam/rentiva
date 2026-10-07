<?php
/**
 * Thankyou page — Rentiva override.
 *
 * Based on WooCommerce core's checkout/thankyou.php (v8.1.0): same hooks and
 * data access, reskinned into the card-based design used across the booking
 * flow instead of WooCommerce's plain notice + <ul> overview.
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="rbfw-order-received woocommerce-order">

	<?php
	if ( $order ) :

		do_action( 'woocommerce_before_thankyou', $order->get_id() );
		?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'woocommerce' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>

			<?php wc_get_template( 'checkout/order-received.php', array( 'order' => $order ) ); ?>

			<div class="rbfw-or-card">
				<div class="rbfw-or-card-label"><?php esc_html_e( 'Order Information', 'woocommerce' ); ?></div>
				<div class="rbfw-or-grid">
					<div class="rbfw-or-grid-item">
						<div class="rbfw-or-grid-label"><?php esc_html_e( 'Order number', 'woocommerce' ); ?></div>
						<div class="rbfw-or-grid-value"><?php echo esc_html( $order->get_order_number() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					</div>
					<div class="rbfw-or-grid-item">
						<div class="rbfw-or-grid-label"><?php esc_html_e( 'Date', 'woocommerce' ); ?></div>
						<div class="rbfw-or-grid-value"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></div>
					</div>
					<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
					<div class="rbfw-or-grid-item">
						<div class="rbfw-or-grid-label"><?php esc_html_e( 'Email', 'woocommerce' ); ?></div>
						<div class="rbfw-or-grid-value"><?php echo esc_html( $order->get_billing_email() ); ?></div>
					</div>
					<?php endif; ?>
					<div class="rbfw-or-grid-item">
						<div class="rbfw-or-grid-label"><?php esc_html_e( 'Total', 'woocommerce' ); ?></div>
						<div class="rbfw-or-grid-value"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></div>
					</div>
					<?php if ( $order->get_payment_method_title() ) : ?>
					<div class="rbfw-or-grid-item">
						<div class="rbfw-or-grid-label"><?php esc_html_e( 'Payment method', 'woocommerce' ); ?></div>
						<div class="rbfw-or-grid-value"><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></div>
					</div>
					<?php endif; ?>
				</div>
			</div>

		<?php endif; ?>

		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php else : ?>

		<?php wc_get_template( 'checkout/order-received.php', array( 'order' => false ) ); ?>

	<?php endif; ?>

</div>
