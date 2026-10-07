<?php
/**
 * Order details — Rentiva override.
 *
 * Based on WooCommerce core's order/order-details.php (v10.9.0): same data
 * (order items, totals, actions, customer note, downloads) and hooks, reskinned
 * into the card-based "Booking Details" / "Price Summary" layout instead of a
 * two-column <table>.
 *
 * @var bool $show_downloads Controls whether the downloads table should be rendered.
 */

// phpcs:disable WooCommerce.Commenting.CommentHooks.MissingHookComment

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if ( ! $order ) {
	return;
}

$order_items        = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note  = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$downloads           = $order->get_downloadable_items();
$actions             = array_filter(
	wc_get_account_orders_actions( $order ),
	function ( $key ) {
		return 'view' !== $key;
	},
	ARRAY_FILTER_USE_KEY
);

// We make sure the order belongs to the user. This will also be true if the user is a guest, and the order belongs to a guest (userID === 0).
$show_customer_details = $order->get_user_id() === get_current_user_id();

if ( $show_downloads ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}
?>
<section class="woocommerce-order-details">
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	<div class="rbfw-or-card">
		<div class="rbfw-or-card-label"><?php esc_html_e( 'Booking Details', 'woocommerce' ); ?></div>

		<?php
		do_action( 'woocommerce_order_details_before_order_table_items', $order );

		$rbfw_item_count = 0;
		foreach ( $order_items as $item_id => $item ) {
			$product = $item->get_product();

			if ( $rbfw_item_count > 0 ) {
				echo '<div class="rbfw-or-divider"></div>';
			}
			++$rbfw_item_count;

			wc_get_template(
				'order/order-details-item.php',
				array(
					'order'              => $order,
					'item_id'            => $item_id,
					'item'               => $item,
					'show_purchase_note' => $show_purchase_note,
					'purchase_note'      => $product ? $product->get_purchase_note() : '',
					'product'            => $product,
				)
			);
		}

		do_action( 'woocommerce_order_details_after_order_table_items', $order );
		?>
	</div>

	<div class="rbfw-or-card rbfw-or-summary">
		<div class="rbfw-or-card-label"><?php esc_html_e( 'Price Summary', 'woocommerce' ); ?></div>
		<?php
		foreach ( $order->get_order_item_totals() as $key => $total ) {
			// 'order_total' (WC_Abstract_Order::add_order_item_totals_total_row()) is the
			// grand-total row regardless of where it falls in the array — the "payment
			// method" row can be added either before or after it depending on whether the
			// 'email_improvements' feature is enabled, so position isn't a reliable signal.
			$rbfw_is_grand_total = ( 'order_total' === $key );
			?>
			<div class="rbfw-or-row<?php echo $rbfw_is_grand_total ? ' rbfw-or-row-total' : ''; ?>">
				<span class="rbfw-or-row-label"><?php echo esc_html( $total['label'] ); ?></span>
				<span class="rbfw-or-row-value"><?php echo wp_kses_post( $total['value'] ); ?></span>
			</div>
			<?php
		}
		?>
	</div>

	<?php if ( $order->get_customer_note() ) : ?>
	<div class="rbfw-or-card">
		<div class="rbfw-or-card-label"><?php esc_html_e( 'Note', 'woocommerce' ); ?></div>
		<?php
		$customer_note = wc_wptexturize_order_note( $order->get_customer_note() );
		echo wp_kses( nl2br( $customer_note ), array( 'br' => array() ) );
		?>
	</div>
	<?php endif; ?>

	<?php if ( ! empty( $actions ) ) : ?>
	<div class="rbfw-or-actions">
		<?php
		foreach ( $actions as $key => $action ) {
			if ( empty( $action['aria-label'] ) ) {
				/* translators: %1$s Action name, %2$s Order number. */
				$action_aria_label = sprintf( __( '%1$s order number %2$s', 'woocommerce' ), $action['name'], $order->get_order_number() );
			} else {
				$action_aria_label = $action['aria-label'];
			}
			echo '<a href="' . esc_url( $action['url'] ) . '" class="rbfw-or-btn ' . sanitize_html_class( $key ) . ' order-actions-button" aria-label="' . esc_attr( $action_aria_label ) . '">' . esc_html( $action['name'] ) . '</a>';
			unset( $action_aria_label );
		}
		?>
	</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
</section>

<?php
/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param WC_Order $order Order data.
 */
do_action( 'woocommerce_after_order_details', $order );

if ( $show_customer_details ) {
	wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) );
}
