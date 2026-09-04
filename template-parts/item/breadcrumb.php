<?php
/**
 * Single-item breadcrumb — Home / Category / Item title.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_item_id    = isset( $args['item_id'] ) ? (int) $args['item_id'] : get_the_ID();
$rentiva_categories = Rentiva_Rental_Adapter::get_categories( $rentiva_item_id );
?>
<div class="rentiva-container">
	<nav class="rentiva-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'rentiva' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'rentiva' ); ?></a>
		<span aria-hidden="true">/</span>
		<a href="<?php echo esc_url( rentiva_get_rentals_page_url() ); ?>"><?php esc_html_e( 'Rentals', 'rentiva' ); ?></a>
		<?php foreach ( $rentiva_categories as $rentiva_term ) : ?>
			<span aria-hidden="true">/</span>
			<a href="<?php echo esc_url( get_term_link( $rentiva_term ) ); ?>"><?php echo esc_html( $rentiva_term->name ); ?></a>
		<?php endforeach; ?>
		<span aria-hidden="true">/</span>
		<span class="rentiva-breadcrumb__current"><?php echo esc_html( get_the_title( $rentiva_item_id ) ); ?></span>
	</nav>
</div>
