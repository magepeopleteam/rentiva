<?php
/**
 * Single-item gallery — main image + like/grid icons + photo-count pill +
 * thumbnail strip. Matches mockup/src/pages/DetailPage.tsx's gallery block.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_item_id = isset( $args['item_id'] ) ? (int) $args['item_id'] : get_the_ID();
$rentiva_gallery  = Rentiva_Rental_Adapter::get_gallery_ids( $rentiva_item_id );
$rentiva_title    = get_the_title( $rentiva_item_id );
?>
<div class="rentiva-gallery">
	<div class="rentiva-gallery__main">
		<?php if ( ! empty( $rentiva_gallery ) ) : ?>
			<?php echo wp_get_attachment_image( $rentiva_gallery[0], 'rentiva-gallery-main', false, array( 'class' => 'rentiva-gallery__image js-rentiva-gallery-main', 'alt' => $rentiva_title ) ); ?>
		<?php else : ?>
			<span class="rentiva-gallery__image rentiva-gallery__image--placeholder js-rentiva-gallery-main" aria-hidden="true"></span>
		<?php endif; ?>

		<div class="rentiva-gallery__actions">
			<button
				type="button"
				class="rentiva-icon-btn js-rentiva-favorite"
				data-rentiva-favorite-id="<?php echo esc_attr( $rentiva_item_id ); ?>"
				aria-pressed="false"
			>
				<span class="screen-reader-text"><?php esc_html_e( 'Save to favorites', 'rentiva' ); ?></span>
				<?php echo rentiva_get_icon( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<button type="button" class="rentiva-icon-btn">
				<span class="screen-reader-text"><?php esc_html_e( 'Gallery view', 'rentiva' ); ?></span>
				<?php echo rentiva_get_icon( 'grid' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>

		<?php if ( count( $rentiva_gallery ) > 1 ) : ?>
			<span class="rentiva-gallery__count">
				<?php
				printf(
					/* translators: %d: number of photos */
					esc_html( _n( '%d Photo', '%d Photos', count( $rentiva_gallery ), 'rentiva' ) ),
					count( $rentiva_gallery )
				);
				?>
			</span>
		<?php endif; ?>
	</div>

	<?php if ( count( $rentiva_gallery ) > 1 ) : ?>
		<div class="rentiva-gallery__thumbs">
			<?php foreach ( $rentiva_gallery as $rentiva_index => $rentiva_image_id ) : ?>
				<button
					type="button"
					class="rentiva-gallery__thumb js-rentiva-gallery-thumb<?php echo 0 === $rentiva_index ? ' is-active' : ''; ?>"
					data-full-src="<?php echo esc_url( wp_get_attachment_image_url( $rentiva_image_id, 'rentiva-gallery-main' ) ); ?>"
				>
					<?php echo wp_get_attachment_image( $rentiva_image_id, 'rentiva-gallery-thumb', false, array( 'alt' => '' ) ); ?>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
