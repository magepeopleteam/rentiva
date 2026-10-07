<?php
/**
 * Order Item Details — Rentiva override.
 *
 * Based on WooCommerce core's order/order-details-item.php (v5.2.0): same
 * data/hooks (`woocommerce_order_item_visible`, `woocommerce_order_item_name`,
 * `woocommerce_order_item_quantity_html`, the meta-start/meta-end actions),
 * reskinned as a label/value row instead of a <tr><td> pair so it drops into
 * the card-based order-details.php override below it.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
	return;
}
?>
<div class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'rbfw-or-item order_item', $item, $order ) ); ?>">

	<div class="rbfw-or-row rbfw-or-row-main">
		<span class="rbfw-or-row-label">
			<?php
			$is_visible        = $product && $product->is_visible();
			$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

			echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible ) );

			$qty          = $item->get_quantity();
			$refunded_qty = $order->get_qty_refunded_for_item( $item_id );

			if ( $refunded_qty ) {
				$qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
			} else {
				$qty_display = esc_html( $qty );
			}

			echo apply_filters( 'woocommerce_order_item_quantity_html', ' <span class="rbfw-or-item-qty">' . sprintf( '&times;&nbsp;%s', $qty_display ) . '</span>', $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</span>
		<span class="rbfw-or-row-value"><?php echo $order->get_formatted_line_subtotal( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	</div>

	<?php
	do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

	wc_display_item_meta(
		$item,
		array(
			'before'       => '',
			'after'        => '</span></div>',
			'separator'    => '</span></div>',
			'label_before' => '<div class="rbfw-or-row"><span class="rbfw-or-row-label">',
			'label_after'  => '</span><span class="rbfw-or-row-value">',
		)
	);

	do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
	?>

</div>

<?php if ( $show_purchase_note && $purchase_note ) : ?>

	<div class="rbfw-or-item-purchase-note"><?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>

<?php endif; ?>
