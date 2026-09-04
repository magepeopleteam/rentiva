<?php
/**
 * No-results state for index.php/search.php.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="rentiva-empty-state">
	<h2 class="rentiva-h4"><?php esc_html_e( 'Nothing found', 'rentiva' ); ?></h2>
	<?php if ( is_search() ) : ?>
		<p class="rentiva-body">
			<?php esc_html_e( 'Nothing matched your search. Try different keywords.', 'rentiva' ); ?>
		</p>
		<?php get_search_form(); ?>
	<?php else : ?>
		<p class="rentiva-body">
			<?php esc_html_e( 'It looks like nothing has been published here yet.', 'rentiva' ); ?>
		</p>
	<?php endif; ?>
</div>
