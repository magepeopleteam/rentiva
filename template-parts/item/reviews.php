<?php
/**
 * "What renters say" — aggregate score card + review list/form.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_item_id = isset( $args['item_id'] ) ? (int) $args['item_id'] : get_the_ID();
$rentiva_rating   = Rentiva_Rental_Adapter::get_average_rating( $rentiva_item_id );
$rentiva_reviews  = Rentiva_Rental_Adapter::get_review_count( $rentiva_item_id );
?>
<div class="rentiva-item-section rentiva-item-reviews">
	<h2 class="rentiva-h4"><?php esc_html_e( 'What renters say', 'rentiva' ); ?></h2>

	<?php if ( $rentiva_reviews > 0 ) : ?>
		<div class="rentiva-card rentiva-item-reviews__summary">
			<div class="rentiva-item-reviews__score">
				<p class="rentiva-item-reviews__score-value"><?php echo esc_html( number_format_i18n( $rentiva_rating, 1 ) ); ?></p>
				<p class="rentiva-caption"><?php esc_html_e( 'out of 5', 'rentiva' ); ?></p>
			</div>
			<div>
				<?php rentiva_the_star_rating( $rentiva_rating ); ?>
				<p class="rentiva-small">
					<?php echo esc_html( sprintf( /* translators: %d: review count */ _n( '%d review', '%d reviews', $rentiva_reviews, 'rentiva' ), $rentiva_reviews ) ); ?>
				</p>
			</div>
		</div>
	<?php endif; ?>

	<?php Rentiva_Rental_Adapter::render_reviews( $rentiva_item_id ); ?>
</div>
