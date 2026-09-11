<?php
/**
 * Rental items archive — the "browse all rentals" page. `rbfw_item` has a
 * real native archive (no plugin template_include/archive_template
 * interception was found — see docs/booking-integration.md), so this file
 * is picked up by WordPress's normal template hierarchy; no page-template
 * or filter override is needed.
 *
 * Listing-page layout: a left filter sidebar (item-name search, category,
 * location — Rentiva_Rental_Adapter::query_items() already accepts array
 * terms for both taxonomies, so the single hero/select filter and these
 * checkboxes share one code path) plus a result toolbar (count, sort,
 * grid/list view) on the right.
 *
 * Every control degrades to a plain GET request with no JS required:
 * - Sort and both checkbox groups live in one <form method="get">; the
 *   sidebar's "Apply Filters" button submits it. assets/js/archive-filters.js
 *   only adds auto-submit-on-change to the sort <select> as a convenience.
 * - Grid/list is a `view` query param rendered as two plain links, not a
 *   client-side toggle, so it's bookmarkable/shareable and needs no JS.
 * - The sidebar collapses into a native <details>/<summary> disclosure on
 *   mobile only (summary hidden via CSS at desktop widths, where the panel
 *   stays in its default `open` state) — no JS involved either.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only filters, no state change.
// "item_category", never the real taxonomy slug "rbfw_item_caregory" —
// WordPress registers that as the taxonomy's own query var, so a request
// carrying it flips is_tax() true (and the plugin's own taxonomy template
// takes over) instead of reaching this file at all. query_items() below
// still passes the selected slugs to its 'category' arg, which builds its
// own explicit tax_query against the real taxonomy — a separate, safe thing
// from the URL's own query string.
$rentiva_categories_selected = isset( $_GET['item_category'] )
	? array_values( array_filter( array_map( 'sanitize_title', (array) wp_unslash( $_GET['item_category'] ) ) ) )
	: array();
$rentiva_locations_selected = isset( $_GET['location'] )
	? array_values( array_filter( array_map( 'sanitize_title', (array) wp_unslash( $_GET['location'] ) ) ) )
	: array();
// "item_search", never WordPress's reserved "s" — a request carrying "s"
// flips is_search() to true, which outranks is_post_type_archive() in the
// template hierarchy and serves search.php instead of this file entirely.
// query_items() below still passes this to WP_Query's own internal 's' arg
// (Rentiva_Rental_Adapter::query_items()'s 'search' key) — that's a separate,
// safe thing from the URL's own query string.
$rentiva_search = isset( $_GET['item_search'] ) ? sanitize_text_field( wp_unslash( $_GET['item_search'] ) ) : '';
$rentiva_view   = ( isset( $_GET['view'] ) && 'list' === $_GET['view'] ) ? 'list' : 'grid';
$rentiva_paged  = max( 1, (int) get_query_var( 'paged' ) );

$rentiva_sort_options = array(
	'newest'     => array(
		'label'   => __( 'Newest first', 'rentiva' ),
		'orderby' => 'date',
		'order'   => 'DESC',
	),
	'oldest'     => array(
		'label'   => __( 'Oldest first', 'rentiva' ),
		'orderby' => 'date',
		'order'   => 'ASC',
	),
	'title-asc'  => array(
		'label'   => __( 'Name: A to Z', 'rentiva' ),
		'orderby' => 'title',
		'order'   => 'ASC',
	),
	'title-desc' => array(
		'label'   => __( 'Name: Z to A', 'rentiva' ),
		'orderby' => 'title',
		'order'   => 'DESC',
	),
);
$rentiva_sort = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'newest';
if ( ! isset( $rentiva_sort_options[ $rentiva_sort ] ) ) {
	$rentiva_sort = 'newest';
}
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$rentiva_query = Rentiva_Rental_Adapter::query_items(
	array(
		'category' => $rentiva_categories_selected,
		'location' => $rentiva_locations_selected,
		'search'   => $rentiva_search,
		'orderby'  => $rentiva_sort_options[ $rentiva_sort ]['orderby'],
		'order'    => $rentiva_sort_options[ $rentiva_sort ]['order'],
		'paged'    => $rentiva_paged,
		'per_page' => 12,
	)
);

$rentiva_cards = array();
foreach ( $rentiva_query->posts as $rentiva_post ) {
	$rentiva_cards[] = Rentiva_Rental_Adapter::get_item_card_data( $rentiva_post );
}
$rentiva_total_found = (int) $rentiva_query->found_posts;
wp_reset_postdata();

// Sidebar checkbox options — only terms with a published item attached, so
// every checkbox shown can actually change the result set.
$rentiva_category_terms = rentiva_has_booking_plugin() ? get_terms( array( 'taxonomy' => Rentiva_Rental_Adapter::TAXONOMY_CATEGORY, 'hide_empty' => true ) ) : array();
$rentiva_location_terms = rentiva_has_booking_plugin() ? get_terms( array( 'taxonomy' => Rentiva_Rental_Adapter::TAXONOMY_LOCATION, 'hide_empty' => true ) ) : array();
if ( is_wp_error( $rentiva_category_terms ) ) {
	$rentiva_category_terms = array();
}
if ( is_wp_error( $rentiva_location_terms ) ) {
	$rentiva_location_terms = array();
}

$rentiva_active_filter_count = count( $rentiva_categories_selected ) + count( $rentiva_locations_selected ) + ( $rentiva_search ? 1 : 0 );
$rentiva_rentals_url          = rentiva_get_rentals_page_url();

// Every pagination/sort/view link below reuses the current query string, so
// an explicit 'base'/'add_args' keeps paginate_links() carrying s/category/
// location/orderby/view across pages instead of dropping them.
$rentiva_pagination_base = esc_url_raw( add_query_arg( 'paged', '%#%' ) );
?>

<header class="rentiva-archive-header">
	<div class="rentiva-container">
		<h1 class="rentiva-h2"><?php esc_html_e( 'Browse Rentals', 'rentiva' ); ?></h1>
		<p class="rentiva-lead"><?php esc_html_e( 'Find the right equipment for your next adventure.', 'rentiva' ); ?></p>
	</div>
</header>

<div class="rentiva-container rentiva-section--tight">
	<div class="rentiva-archive-layout">
		<aside class="rentiva-archive-sidebar">
			<form id="rentiva-archive-filters-form" class="rentiva-archive-filters" method="get" action="<?php echo esc_url( $rentiva_rentals_url ); ?>">
				<?php if ( 'list' === $rentiva_view ) : ?>
					<input type="hidden" name="view" value="list">
				<?php endif; ?>

				<details class="rentiva-archive-sidebar__panel" open>
					<summary class="rentiva-archive-sidebar__summary">
						<span class="rentiva-archive-sidebar__summary-label">
							<?php echo rentiva_get_icon( 'list' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php esc_html_e( 'Filters', 'rentiva' ); ?>
							<?php if ( $rentiva_active_filter_count > 0 ) : ?>
								<span class="rentiva-filter-badge"><?php echo (int) $rentiva_active_filter_count; ?></span>
							<?php endif; ?>
						</span>
						<?php echo rentiva_get_icon( 'chevron-right', array( 'class' => 'rentiva-archive-sidebar__caret' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</summary>

					<div class="rentiva-archive-sidebar__body">
						<div class="rentiva-filter-group rentiva-field--autocomplete" data-rentiva-autocomplete="items">
							<label class="rentiva-filter-group__title" for="rentiva-archive-search"><?php esc_html_e( 'Item Name', 'rentiva' ); ?></label>
							<div class="rentiva-field__input-wrap">
								<span class="rentiva-field__icon"><?php echo rentiva_get_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<input
									class="rentiva-input"
									type="text"
									id="rentiva-archive-search"
									name="item_search"
									value="<?php echo esc_attr( $rentiva_search ); ?>"
									placeholder="<?php esc_attr_e( 'Search item name…', 'rentiva' ); ?>"
									autocomplete="off"
									role="combobox"
									aria-expanded="false"
									aria-autocomplete="list"
									aria-controls="rentiva-archive-search-results"
								>
								<button type="button" class="rentiva-autocomplete-clear" aria-label="<?php esc_attr_e( 'Clear search', 'rentiva' ); ?>" hidden>
									<?php echo rentiva_get_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</button>
							</div>
							<ul class="rentiva-autocomplete-list" id="rentiva-archive-search-results" role="listbox" hidden></ul>
						</div>

						<?php if ( ! empty( $rentiva_category_terms ) ) : ?>
							<div class="rentiva-filter-group">
								<h3 class="rentiva-filter-group__title"><?php esc_html_e( 'Category', 'rentiva' ); ?></h3>
								<div class="rentiva-filter-group__options">
									<?php foreach ( $rentiva_category_terms as $rentiva_term ) : ?>
										<label class="rentiva-filter-checkbox">
											<input type="checkbox" name="item_category[]" value="<?php echo esc_attr( $rentiva_term->slug ); ?>" <?php checked( in_array( $rentiva_term->slug, $rentiva_categories_selected, true ) ); ?>>
											<span class="rentiva-filter-checkbox__label"><?php echo esc_html( $rentiva_term->name ); ?></span>
											<span class="rentiva-filter-checkbox__count"><?php echo esc_html( $rentiva_term->count ); ?></span>
										</label>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $rentiva_location_terms ) ) : ?>
							<div class="rentiva-filter-group">
								<h3 class="rentiva-filter-group__title"><?php esc_html_e( 'Location', 'rentiva' ); ?></h3>
								<div class="rentiva-filter-group__options">
									<?php foreach ( $rentiva_location_terms as $rentiva_term ) : ?>
										<label class="rentiva-filter-checkbox">
											<input type="checkbox" name="location[]" value="<?php echo esc_attr( $rentiva_term->slug ); ?>" <?php checked( in_array( $rentiva_term->slug, $rentiva_locations_selected, true ) ); ?>>
											<span class="rentiva-filter-checkbox__label"><?php echo esc_html( $rentiva_term->name ); ?></span>
											<span class="rentiva-filter-checkbox__count"><?php echo esc_html( $rentiva_term->count ); ?></span>
										</label>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

						<div class="rentiva-filter-actions">
							<button type="submit" class="btn btn--primary rentiva-filter-actions__apply">
								<?php esc_html_e( 'Apply Filters', 'rentiva' ); ?>
							</button>
							<?php if ( $rentiva_active_filter_count > 0 ) : ?>
								<a href="<?php echo esc_url( $rentiva_rentals_url ); ?>" class="rentiva-filter-actions__clear">
									<?php esc_html_e( 'Clear all', 'rentiva' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</details>
			</form>
		</aside>

		<div class="rentiva-archive-results<?php echo 'list' === $rentiva_view ? ' is-list-view' : ''; ?>">
			<div class="rentiva-archive-toolbar">
				<p class="rentiva-archive-toolbar__count">
					<?php
					printf(
						/* translators: %s: number of rentals found, already formatted. */
						esc_html( _n( '%s rental found', '%s rentals found', $rentiva_total_found, 'rentiva' ) ),
						'<strong>' . esc_html( number_format_i18n( $rentiva_total_found ) ) . '</strong>'
					);
					?>
				</p>

				<div class="rentiva-archive-toolbar__controls">
					<div class="rentiva-archive-sort">
						<label for="rentiva-archive-sort" class="screen-reader-text"><?php esc_html_e( 'Sort by', 'rentiva' ); ?></label>
						<select id="rentiva-archive-sort" class="rentiva-select" name="orderby" form="rentiva-archive-filters-form">
							<?php foreach ( $rentiva_sort_options as $rentiva_sort_key => $rentiva_sort_option ) : ?>
								<option value="<?php echo esc_attr( $rentiva_sort_key ); ?>" <?php selected( $rentiva_sort, $rentiva_sort_key ); ?>>
									<?php echo esc_html( $rentiva_sort_option['label'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="rentiva-archive-view-toggle" role="group" aria-label="<?php esc_attr_e( 'Layout', 'rentiva' ); ?>">
						<a
							href="<?php echo esc_url( add_query_arg( 'view', 'grid' ) ); ?>"
							class="rentiva-archive-view-toggle__btn<?php echo 'grid' === $rentiva_view ? ' is-active' : ''; ?>"
							aria-pressed="<?php echo 'grid' === $rentiva_view ? 'true' : 'false'; ?>"
						>
							<?php echo rentiva_get_icon( 'grid' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span class="screen-reader-text"><?php esc_html_e( 'Grid view', 'rentiva' ); ?></span>
						</a>
						<a
							href="<?php echo esc_url( add_query_arg( 'view', 'list' ) ); ?>"
							class="rentiva-archive-view-toggle__btn<?php echo 'list' === $rentiva_view ? ' is-active' : ''; ?>"
							aria-pressed="<?php echo 'list' === $rentiva_view ? 'true' : 'false'; ?>"
						>
							<?php echo rentiva_get_icon( 'list' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span class="screen-reader-text"><?php esc_html_e( 'List view', 'rentiva' ); ?></span>
						</a>
					</div>
				</div>
			</div>

			<?php rentiva_rental_grid( $rentiva_cards, '3' ); ?>

			<?php if ( $rentiva_query->max_num_pages > 1 ) : ?>
				<div class="rentiva-archive__pagination">
					<?php
					echo paginate_links( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links() output is already escaped.
						array(
							'base'      => $rentiva_pagination_base,
							'format'    => '',
							'total'     => $rentiva_query->max_num_pages,
							'current'   => $rentiva_paged,
							'mid_size'  => 2,
							'prev_text' => rentiva_get_icon( 'chevron-left' ) . '<span class="screen-reader-text">' . esc_html__( 'Previous', 'rentiva' ) . '</span>',
							'next_text' => rentiva_get_icon( 'chevron-right' ) . '<span class="screen-reader-text">' . esc_html__( 'Next', 'rentiva' ) . '</span>',
						)
					);
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php
get_footer();
