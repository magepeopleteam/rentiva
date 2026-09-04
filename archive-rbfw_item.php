<?php
/**
 * Rental items archive — the "browse all rentals" page. `rbfw_item` has a
 * real native archive (no plugin template_include/archive_template
 * interception was found — see docs/booking-integration.md), so this file
 * is picked up by WordPress's normal template hierarchy; no page-template
 * or filter override is needed.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$rentiva_category = isset( $_GET['rbfw_item_caregory'] ) ? sanitize_title( wp_unslash( $_GET['rbfw_item_caregory'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter, no state change.
$rentiva_location  = isset( $_GET['location'] ) ? sanitize_text_field( wp_unslash( $_GET['location'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$rentiva_search    = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$rentiva_paged     = max( 1, (int) get_query_var( 'paged' ) );

$rentiva_query = Rentiva_Rental_Adapter::query_items(
	array(
		'category' => $rentiva_category,
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
		<h1 class="rentiva-h2"><?php esc_html_e( 'Browse Rentals', 'rentiva' ); ?></h1>
		<p class="rentiva-lead"><?php esc_html_e( 'Find the right equipment for your next adventure.', 'rentiva' ); ?></p>

		<form class="rentiva-archive-filters" method="get" action="<?php echo esc_url( rentiva_get_rentals_page_url() ); ?>">
			<div class="rentiva-field rentiva-archive-filters__field">
				<label class="rentiva-field__label" for="rentiva-archive-search"><?php esc_html_e( 'Search', 'rentiva' ); ?></label>
				<input class="rentiva-input" type="text" id="rentiva-archive-search" name="s" value="<?php echo esc_attr( $rentiva_search ); ?>" placeholder="<?php esc_attr_e( 'Search rentals…', 'rentiva' ); ?>">
			</div>
			<div class="rentiva-field rentiva-archive-filters__field">
				<label class="rentiva-field__label" for="rentiva-archive-category"><?php esc_html_e( 'Category', 'rentiva' ); ?></label>
				<select class="rentiva-select" id="rentiva-archive-category" name="rbfw_item_caregory">
					<option value=""><?php esc_html_e( 'All categories', 'rentiva' ); ?></option>
					<?php
					$rentiva_terms = rentiva_has_booking_plugin() ? get_terms( array( 'taxonomy' => Rentiva_Rental_Adapter::TAXONOMY_CATEGORY, 'hide_empty' => false ) ) : array();
					if ( ! is_wp_error( $rentiva_terms ) ) :
						foreach ( $rentiva_terms as $rentiva_term ) :
							?>
							<option value="<?php echo esc_attr( $rentiva_term->slug ); ?>" <?php selected( $rentiva_category, $rentiva_term->slug ); ?>>
								<?php echo esc_html( $rentiva_term->name ); ?>
							</option>
							<?php
						endforeach;
					endif;
					?>
				</select>
			</div>
			<button type="submit" class="btn btn--primary rentiva-archive-filters__submit">
				<?php esc_html_e( 'Filter', 'rentiva' ); ?>
			</button>
		</form>
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
					'mid_size'  => 2,
					'prev_text' => rentiva_get_icon( 'chevron-left' ) . '<span class="screen-reader-text">' . esc_html__( 'Previous', 'rentiva' ) . '</span>',
					'next_text' => rentiva_get_icon( 'chevron-right' ) . '<span class="screen-reader-text">' . esc_html__( 'Next', 'rentiva' ) . '</span>',
				)
			);
			?>
		</div>
	<?php endif; ?>
</div>

<?php
get_footer();
