<?php
/**
 * One rental item card — used by Popular Rentals, the archive grid, and
 * Similar Rentals. Matches mockup's <RentalCard>/<SimilarCard> design.
 *
 * Expected $args:
 *   id          int    Item ID (rbfw_item), used as the favorites key.
 *   title       string
 *   url         string
 *   image_id    int    Optional attachment ID.
 *   type        string Badge label, e.g. "Mountain Bike".
 *   rating      float  0–5.
 *   reviews     int    Review count.
 *   location    string
 *   price       string Already-formatted price (see Rentiva_Rental_Adapter::get_display_price()).
 *   price_unit  string e.g. "/ day".
 *   compact     bool   Smaller variant used for Similar Rentals.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_id       = isset( $args['id'] ) ? (int) $args['id'] : 0;
$rentiva_title    = isset( $args['title'] ) ? $args['title'] : '';
$rentiva_url      = isset( $args['url'] ) ? $args['url'] : '#';
$rentiva_image_id = isset( $args['image_id'] ) ? (int) $args['image_id'] : 0;
$rentiva_type     = isset( $args['type'] ) ? $args['type'] : '';
$rentiva_rating   = isset( $args['rating'] ) ? (float) $args['rating'] : 0;
$rentiva_reviews  = isset( $args['reviews'] ) ? (int) $args['reviews'] : 0;
$rentiva_location = isset( $args['location'] ) ? $args['location'] : '';
$rentiva_price    = isset( $args['price'] ) ? $args['price'] : '';
$rentiva_unit     = isset( $args['price_unit'] ) ? $args['price_unit'] : __( '/ day', 'rentiva' );
$rentiva_compact  = ! empty( $args['compact'] );
?>
<div class="rentiva-card rentiva-card--hover rentiva-rental-card<?php echo $rentiva_compact ? ' rentiva-rental-card--compact' : ''; ?>">
	<a href="<?php echo esc_url( $rentiva_url ); ?>" class="rentiva-rental-card__media">
		<?php if ( $rentiva_image_id ) : ?>
			<?php echo wp_get_attachment_image( $rentiva_image_id, 'rentiva-card', false, array( 'class' => 'rentiva-rental-card__image', 'alt' => $rentiva_title ) ); ?>
		<?php else : ?>
			<span class="rentiva-rental-card__image rentiva-rental-card__image--placeholder" aria-hidden="true"></span>
		<?php endif; ?>

		<?php if ( $rentiva_type ) : ?>
			<span class="rentiva-rental-card__badge"><?php echo esc_html( $rentiva_type ); ?></span>
		<?php endif; ?>
	</a>

	<button
		type="button"
		class="rentiva-icon-btn rentiva-rental-card__like js-rentiva-favorite"
		data-rentiva-favorite-id="<?php echo esc_attr( $rentiva_id ); ?>"
		aria-pressed="false"
	>
		<span class="screen-reader-text"><?php esc_html_e( 'Save to favorites', 'rentiva' ); ?></span>
		<?php echo rentiva_get_icon( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>

	<div class="rentiva-rental-card__body">
		<h3 class="rentiva-rental-card__title">
			<a href="<?php echo esc_url( $rentiva_url ); ?>"><?php echo esc_html( $rentiva_title ); ?></a>
		</h3>

		<?php if ( $rentiva_rating > 0 ) : ?>
			<div class="rentiva-rating">
				<?php rentiva_the_star_rating( $rentiva_rating ); ?>
				<span><?php echo esc_html( number_format_i18n( $rentiva_rating, 1 ) ); ?></span>
				<span class="rentiva-rating__count">(<?php echo esc_html( $rentiva_reviews ); ?>)</span>
			</div>
		<?php endif; ?>

		<?php if ( $rentiva_location ) : ?>
			<div class="rentiva-rental-card__location">
				<?php echo rentiva_get_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php echo esc_html( $rentiva_location ); ?></span>
			</div>
		<?php endif; ?>

		<div class="rentiva-rental-card__footer">
			<div class="rentiva-rental-card__price">
				<span class="rentiva-rental-card__price-value"><?php echo wp_kses_post( $rentiva_price ); ?></span>
				<span class="rentiva-rental-card__price-unit"><?php echo esc_html( $rentiva_unit ); ?></span>
			</div>
			<a href="<?php echo esc_url( $rentiva_url ); ?>" class="btn btn--primary rentiva-rental-card__cta">
				<?php esc_html_e( 'View Details', 'rentiva' ); ?>
			</a>
		</div>
	</div>
</div>
