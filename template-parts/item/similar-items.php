<?php
/**
 * "You might also like" — 4-card grid of similar rentals.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_item_id = isset( $args['item_id'] ) ? (int) $args['item_id'] : get_the_ID();
$rentiva_items    = class_exists( 'Rentiva_Rental_Adapter' ) ? Rentiva_Rental_Adapter::get_similar_items( $rentiva_item_id, 4 ) : array();

if ( empty( $rentiva_items ) ) {
	return;
}
?>
<section class="rentiva-section rentiva-section--tight rentiva-similar-items">
	<div class="rentiva-container">
		<h2 class="rentiva-h3" style="margin-bottom:2rem;"><?php esc_html_e( 'You might also like', 'rentiva' ); ?></h2>
		<?php rentiva_rental_grid( $rentiva_items, '4', true ); ?>
	</div>
</section>
