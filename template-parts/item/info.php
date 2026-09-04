<?php
/**
 * Single-item info block — badge, title, rating/location, description,
 * feature pills, price. Matches mockup/src/pages/DetailPage.tsx.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_item_id  = isset( $args['item_id'] ) ? (int) $args['item_id'] : get_the_ID();
$rentiva_subtitle = Rentiva_Rental_Adapter::get_subtitle( $rentiva_item_id );
$rentiva_rating   = Rentiva_Rental_Adapter::get_average_rating( $rentiva_item_id );
$rentiva_reviews  = Rentiva_Rental_Adapter::get_review_count( $rentiva_item_id );
$rentiva_location = Rentiva_Rental_Adapter::get_location_label( $rentiva_item_id );
$rentiva_price    = Rentiva_Rental_Adapter::get_display_price( $rentiva_item_id );
$rentiva_price_2  = Rentiva_Rental_Adapter::get_secondary_price( $rentiva_item_id );
$rentiva_specs    = Rentiva_Rental_Adapter::get_specs( $rentiva_item_id );
$rentiva_pills    = array_slice( $rentiva_specs, 0, 4 );
?>
<div class="rentiva-item-info">
	<?php if ( $rentiva_subtitle ) : ?>
		<span class="rentiva-badge rentiva-badge--primary rentiva-item-info__badge"><?php echo esc_html( strtoupper( $rentiva_subtitle ) ); ?></span>
	<?php endif; ?>

	<h1 class="rentiva-h3 rentiva-item-info__title"><?php the_title(); ?></h1>

	<div class="rentiva-item-info__meta">
		<?php if ( $rentiva_rating > 0 ) : ?>
			<div class="rentiva-rating">
				<?php rentiva_the_star_rating( $rentiva_rating ); ?>
				<span><?php echo esc_html( number_format_i18n( $rentiva_rating, 1 ) ); ?></span>
				<span class="rentiva-rating__count">
					· <?php echo esc_html( sprintf( /* translators: %d: review count */ _n( '%d review', '%d reviews', $rentiva_reviews, 'rentiva' ), $rentiva_reviews ) ); ?>
				</span>
			</div>
		<?php endif; ?>

		<?php if ( $rentiva_location ) : ?>
			<div class="rentiva-item-info__location">
				<?php echo rentiva_get_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $rentiva_location ); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<div class="rentiva-item-info__description rentiva-body">
		<?php the_excerpt(); ?>
	</div>

	<?php if ( ! empty( $rentiva_pills ) ) : ?>
		<div class="rentiva-item-info__pills">
			<?php foreach ( $rentiva_pills as $rentiva_pill ) : ?>
				<span class="rentiva-pill">
					<?php echo esc_html( $rentiva_pill['value'] ? $rentiva_pill['value'] : $rentiva_pill['label'] ); ?>
				</span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="rentiva-item-info__price">
		<span class="rentiva-item-info__price-amount"><?php echo wp_kses_post( $rentiva_price['formatted'] ); ?></span>
		<span class="rentiva-item-info__price-unit"><?php echo esc_html( $rentiva_price['unit'] ); ?></span>
		<?php if ( $rentiva_price_2 ) : ?>
			<span class="rentiva-pill rentiva-pill--muted">
				<?php echo wp_kses_post( $rentiva_price_2['formatted'] ); ?> <?php echo esc_html( $rentiva_price_2['unit'] ); ?>
			</span>
		<?php endif; ?>
	</div>
</div>
