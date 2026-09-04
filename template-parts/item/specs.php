<?php
/**
 * "Key Specifications" grid — reads the plugin's own Feature List field
 * (`rbfw_feature_category`, edited in the item's Modern Editor) via
 * Rentiva_Rental_Adapter::get_specs(). See docs/customization.md for the
 * "Label: Value" entry convention this grid expects.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_item_id = isset( $args['item_id'] ) ? (int) $args['item_id'] : get_the_ID();
$rentiva_specs    = Rentiva_Rental_Adapter::get_specs( $rentiva_item_id );

if ( empty( $rentiva_specs ) ) {
	return;
}
?>
<div class="rentiva-item-section rentiva-item-specs">
	<h2 class="rentiva-h4"><?php esc_html_e( 'Key Specifications', 'rentiva' ); ?></h2>
	<div class="rentiva-item-specs__grid">
		<?php foreach ( $rentiva_specs as $rentiva_spec ) : ?>
			<div class="rentiva-item-specs__cell">
				<p class="rentiva-item-specs__label"><?php echo esc_html( $rentiva_spec['label'] ); ?></p>
				<?php if ( $rentiva_spec['value'] ) : ?>
					<p class="rentiva-item-specs__value"><?php echo esc_html( $rentiva_spec['value'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
