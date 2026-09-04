<?php
/**
 * One "Explore Categories" tile.
 *
 * Expected $args: name (string), url (string), image_id (int, optional),
 * image_url (string, optional fallback when there's no attachment).
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_name      = isset( $args['name'] ) ? $args['name'] : '';
$rentiva_url       = isset( $args['url'] ) ? $args['url'] : '#';
$rentiva_image_id  = isset( $args['image_id'] ) ? (int) $args['image_id'] : 0;
?>
<a href="<?php echo esc_url( $rentiva_url ); ?>" class="rentiva-category-card">
	<?php if ( $rentiva_image_id ) : ?>
		<?php echo wp_get_attachment_image( $rentiva_image_id, 'rentiva-category', false, array( 'class' => 'rentiva-category-card__image', 'alt' => $rentiva_name ) ); ?>
	<?php else : ?>
		<span class="rentiva-category-card__image rentiva-category-card__image--placeholder" aria-hidden="true"></span>
	<?php endif; ?>
	<span class="rentiva-category-card__gradient" aria-hidden="true"></span>
	<span class="rentiva-category-card__footer">
		<span class="rentiva-category-card__name"><?php echo esc_html( $rentiva_name ); ?></span>
		<span class="rentiva-category-card__arrow" aria-hidden="true">
			<?php echo rentiva_get_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span>
	</span>
</a>
