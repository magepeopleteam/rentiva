<?php
/**
 * No-results state for a rental grid (archive, search, popular rentals with
 * no items yet).
 *
 * Expected $args: context (string, optional) — used only to vary copy.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="rentiva-empty-state">
	<p class="rentiva-h4" style="margin-bottom:0.5rem;"><?php esc_html_e( 'No rentals found', 'rentiva' ); ?></p>
	<p class="rentiva-body"><?php esc_html_e( 'Try a different category, location, or date range.', 'rentiva' ); ?></p>
</div>
