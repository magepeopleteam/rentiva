<?php
/**
 * "About this rental" (post content) + "What's Included" checklist.
 * The checklist reads the plugin's Feature List's second category, if one
 * exists — see Rentiva_Rental_Adapter::get_included_items().
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_item_id = isset( $args['item_id'] ) ? (int) $args['item_id'] : get_the_ID();
$rentiva_included = Rentiva_Rental_Adapter::get_included_items( $rentiva_item_id );
?>
<div class="rentiva-item-section rentiva-item-about">
	<h2 class="rentiva-h4"><?php esc_html_e( 'About this rental', 'rentiva' ); ?></h2>
	<div class="rentiva-body rentiva-item-about__content">
		<?php the_content(); ?>
	</div>

	<?php if ( ! empty( $rentiva_included ) ) : ?>
		<h3 class="rentiva-item-about__subheading"><?php esc_html_e( "What's Included", 'rentiva' ); ?></h3>
		<div class="rentiva-item-about__included">
			<?php foreach ( $rentiva_included as $rentiva_feature ) : ?>
				<div class="rentiva-item-about__included-item">
					<span class="rentiva-checklist__icon rentiva-checklist__icon--sm">
						<?php echo rentiva_get_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<span><?php echo esc_html( $rentiva_feature ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
