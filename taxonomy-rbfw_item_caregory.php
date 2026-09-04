<?php
/**
 * Category archive for rental items. Filename uses the taxonomy's real
 * (permanently misspelled) slug `rbfw_item_caregory` — see
 * docs/booking-integration.md.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$rentiva_term   = get_queried_object();
$rentiva_paged  = max( 1, (int) get_query_var( 'paged' ) );
$rentiva_search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter, no state change.

$rentiva_query = Rentiva_Rental_Adapter::query_items(
	array(
		'category' => $rentiva_term instanceof WP_Term ? $rentiva_term->slug : '',
		'search'   => $rentiva_search,
		'paged'    => $rentiva_paged,
		'per_page' => 12,
	)
);

$rentiva_cards = array();
foreach ( $rentiva_query->posts as $rentiva_post ) {
	$rentiva_cards[] = Rentiva_Rental_Adapter::get_item_card_data( $rentiva_post );
}
wp_reset_postdata();
?>

<header class="rentiva-archive-header">
	<div class="rentiva-container">
		<span class="rentiva-eyebrow"><?php esc_html_e( 'Category', 'rentiva' ); ?></span>
		<h1 class="rentiva-h2"><?php echo esc_html( $rentiva_term instanceof WP_Term ? $rentiva_term->name : '' ); ?></h1>
		<?php if ( $rentiva_term instanceof WP_Term && $rentiva_term->description ) : ?>
			<p class="rentiva-lead"><?php echo esc_html( $rentiva_term->description ); ?></p>
		<?php endif; ?>
	</div>
</header>

<div class="rentiva-container rentiva-section--tight">
	<?php rentiva_rental_grid( $rentiva_cards, '3' ); ?>

	<?php if ( $rentiva_query->max_num_pages > 1 ) : ?>
		<div class="rentiva-archive__pagination">
			<?php
			echo paginate_links( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links() output is already escaped.
				array(
					'total'   => $rentiva_query->max_num_pages,
					'current' => $rentiva_paged,
				)
			);
			?>
		</div>
	<?php endif; ?>
</div>

<?php
get_footer();
