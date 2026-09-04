<?php
/**
 * Search form template.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_search_id = 'search-form-' . wp_unique_id();
?>
<form role="search" method="get" class="rentiva-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $rentiva_search_id ); ?>" class="screen-reader-text">
		<?php esc_html_e( 'Search for:', 'rentiva' ); ?>
	</label>
	<input type="search" id="<?php echo esc_attr( $rentiva_search_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search…', 'rentiva' ); ?>" />
	<button type="submit" class="rentiva-icon-btn">
		<span class="screen-reader-text"><?php esc_html_e( 'Search', 'rentiva' ); ?></span>
		<?php echo rentiva_get_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
</form>
