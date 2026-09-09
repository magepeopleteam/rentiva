<?php
/**
 * Homepage hero — full-bleed photo, floating trust badges, headline with an
 * accented last word, a scroll indicator, and the overlapping search panel.
 * Matches mockup/rentiva.html's hero section exactly (the canonical static
 * reference — supersedes the earlier React/.tsx mockup for this page).
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * $args overrides — set only when this template renders as the
 * Rentiva: Hero Elementor widget (Rentiva_Elementor_Widget_Hero::render()),
 * letting an admin override every piece of hero copy per-instance from the
 * Elementor Content tab. A blank/unset $args value always falls through to
 * the existing Rentiva → Theme Settings value (or its hardcoded default),
 * so the built-in PHP fallback layout (front-page.php calling this template
 * directly with no $args) and existing Theme Settings behavior are both
 * unchanged.
 */
$rentiva_arg = function ( $key, $default ) use ( $args ) {
	return ( isset( $args[ $key ] ) && '' !== $args[ $key ] ) ? $args[ $key ] : $default;
};

$rentiva_hero_image_id = ! empty( $args['image_id'] ) ? (int) $args['image_id'] : (int) rentiva_get_setting( 'hero_image_id', 0 );
$rentiva_eyebrow       = $rentiva_arg( 'eyebrow', rentiva_get_setting( 'hero_eyebrow', __( 'RENT • RIDE • EXPLORE', 'rentiva' ) ) );
$rentiva_title         = $rentiva_arg( 'title', rentiva_get_setting( 'hero_title', __( 'Rent. Ride. Explore.', 'rentiva' ) ) );
$rentiva_subtitle      = $rentiva_arg( 'subtitle', rentiva_get_setting( 'hero_subtitle', __( 'Premium bikes, gear and equipment — ready whenever you are.', 'rentiva' ) ) );

$rentiva_badge1_value = $rentiva_arg( 'badge1_value', __( '4.9 / 5', 'rentiva' ) );
$rentiva_badge1_label = $rentiva_arg( 'badge1_label', __( 'Top Rated', 'rentiva' ) );
$rentiva_badge2_value = $rentiva_arg( 'badge2_value', __( '10,000+', 'rentiva' ) );
$rentiva_badge2_label = $rentiva_arg( 'badge2_label', __( 'Rentals Done', 'rentiva' ) );
$rentiva_live_text    = $rentiva_arg( 'live_text', __( 'Available Now', 'rentiva' ) );

$rentiva_cta_primary_text   = $rentiva_arg( 'cta_primary_text', __( 'Explore Rentals', 'rentiva' ) );
$rentiva_cta_primary_url    = $rentiva_arg( 'cta_primary_url', rentiva_get_rentals_page_url() );
$rentiva_cta_secondary_text = $rentiva_arg( 'cta_secondary_text', __( 'List Your Equipment', 'rentiva' ) );
$rentiva_cta_secondary_url  = $rentiva_arg( 'cta_secondary_url', rentiva_get_list_item_url() );

$rentiva_search_label = $rentiva_arg( 'search_label', __( 'FIND YOUR RENTAL', 'rentiva' ) );
$rentiva_submit_text  = $rentiva_arg( 'submit_text', __( 'Find Rentals', 'rentiva' ) );

$rentiva_field_where_label       = $rentiva_arg( 'field_where_label', __( 'Where', 'rentiva' ) );
$rentiva_field_where_placeholder = $rentiva_arg( 'field_where_placeholder', __( 'Choose location', 'rentiva' ) );
$rentiva_field_pickup_label      = $rentiva_arg( 'field_pickup_label', __( 'Pickup', 'rentiva' ) );
$rentiva_field_return_label      = $rentiva_arg( 'field_return_label', __( 'Return', 'rentiva' ) );
$rentiva_field_category_label    = $rentiva_arg( 'field_category_label', __( 'Category', 'rentiva' ) );

// The last word of the headline is the accented word (e.g. "Explore.") —
// matches the reference design's <span class="accent"> treatment.
$rentiva_title_words  = explode( ' ', trim( $rentiva_title ) );
$rentiva_title_accent = array_pop( $rentiva_title_words );
$rentiva_title_main   = implode( ' ', $rentiva_title_words );

// The subtitle breaks onto a second line at the em dash, if present.
$rentiva_subtitle_lines = array_map( 'trim', explode( '—', $rentiva_subtitle, 2 ) );

// Same 6 featured categories the "Explore What You Need" section shows,
// so the search panel's dropdown and the homepage grid never disagree.
$rentiva_categories = rentiva_get_homepage_categories();
?>
<section class="rentiva-hero">
	<div class="rentiva-hero__media">
		<?php if ( $rentiva_hero_image_id ) : ?>
			<?php echo wp_get_attachment_image( $rentiva_hero_image_id, 'rentiva-hero', false, array( 'class' => 'rentiva-hero__image', 'alt' => '' ) ); ?>
		<?php else : ?>
			<div class="rentiva-hero__image rentiva-hero__image--placeholder" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="rentiva-hero__gradient" aria-hidden="true"></div>
		<div class="rentiva-hero__tint" aria-hidden="true"></div>
	</div>

	<div class="rentiva-hero__badges">
		<div class="rentiva-hero__badge-glass">
			<span class="rentiva-hero__badge-icon">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="#F59E0B"><path d="M8 1l1.85 3.74L14 5.72l-3 2.92.71 4.12L8 10.77l-3.71 1.99.71-4.12-3-2.92 4.15-.98L8 1z"/></svg>
			</span>
			<span>
				<span class="rentiva-hero__badge-val"><?php echo esc_html( $rentiva_badge1_value ); ?></span>
				<span class="rentiva-hero__badge-lbl"><?php echo esc_html( $rentiva_badge1_label ); ?></span>
			</span>
		</div>
		<div class="rentiva-hero__badge-glass">
			<span class="rentiva-hero__badge-icon rentiva-hero__badge-icon--dark">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="1.5"><rect x="2" y="7" width="12" height="7" rx="2"/><path d="M5 7V5a3 3 0 016 0v2"/></svg>
			</span>
			<span>
				<span class="rentiva-hero__badge-val"><?php echo esc_html( $rentiva_badge2_value ); ?></span>
				<span class="rentiva-hero__badge-lbl"><?php echo esc_html( $rentiva_badge2_label ); ?></span>
			</span>
		</div>
		<div class="rentiva-hero__badge-live">
			<span class="rentiva-hero__pulse-dot" aria-hidden="true"></span>
			<span><?php echo esc_html( $rentiva_live_text ); ?></span>
		</div>
	</div>

	<div class="rentiva-hero__content">
		<div class="rentiva-hero__eyebrow">
			<span class="rentiva-hero__eyebrow-dot" aria-hidden="true"></span>
			<span><?php echo esc_html( $rentiva_eyebrow ); ?></span>
		</div>
		<h1 class="rentiva-hero-title">
			<?php echo esc_html( $rentiva_title_main ); ?><br>
			<span class="rentiva-hero-title__accent"><?php echo esc_html( $rentiva_title_accent ); ?></span>
		</h1>
		<p class="rentiva-hero__subtitle">
			<?php echo esc_html( $rentiva_subtitle_lines[0] ); ?><?php if ( isset( $rentiva_subtitle_lines[1] ) ) : ?>&nbsp;—<br><?php echo esc_html( $rentiva_subtitle_lines[1] ); ?><?php endif; ?>
		</p>

		<div class="rentiva-hero__actions">
			<a href="<?php echo esc_url( $rentiva_cta_primary_url ); ?>" class="btn btn--primary btn--lg">
				<?php echo esc_html( $rentiva_cta_primary_text ); ?>
			</a>
			<a href="<?php echo esc_url( $rentiva_cta_secondary_url ); ?>" class="btn btn--glass">
				<?php echo esc_html( $rentiva_cta_secondary_text ); ?>
				<?php echo rentiva_get_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>

		<div class="rentiva-hero__scroll" aria-hidden="true">
			<span class="rentiva-hero__scroll-pill"><span class="rentiva-hero__scroll-pip"></span></span>
		</div>
	</div>

	<div class="rentiva-hero__search-wrap">
		<form class="rentiva-search-panel" method="get" action="<?php echo esc_url( rentiva_get_rentals_page_url() ); ?>">
			<div class="rentiva-search-panel__label-row">
				<span class="rentiva-search-panel__label-dot" aria-hidden="true"></span>
				<span class="rentiva-search-panel__label-text"><?php echo esc_html( $rentiva_search_label ); ?></span>
			</div>
			<div class="rentiva-search-panel__grid">
				<div class="rentiva-field rentiva-search-panel__field">
					<label class="rentiva-field__label" for="rentiva-search-where"><?php echo esc_html( $rentiva_field_where_label ); ?></label>
					<div class="rentiva-field__input-wrap">
						<span class="rentiva-field__icon"><?php echo rentiva_get_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<input class="rentiva-input" type="text" id="rentiva-search-where" name="location" placeholder="<?php echo esc_attr( $rentiva_field_where_placeholder ); ?>">
					</div>
				</div>
				<div class="rentiva-field rentiva-search-panel__field">
					<label class="rentiva-field__label" for="rentiva-search-pickup"><?php echo esc_html( $rentiva_field_pickup_label ); ?></label>
					<div class="rentiva-field__input-wrap">
						<span class="rentiva-field__icon"><?php echo rentiva_get_icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<input class="rentiva-input" type="date" id="rentiva-search-pickup" name="pickup_date" value="<?php echo esc_attr( gmdate( 'Y-m-d', strtotime( '+1 day' ) ) ); ?>">
					</div>
				</div>
				<div class="rentiva-field rentiva-search-panel__field">
					<label class="rentiva-field__label" for="rentiva-search-return"><?php echo esc_html( $rentiva_field_return_label ); ?></label>
					<div class="rentiva-field__input-wrap">
						<span class="rentiva-field__icon"><?php echo rentiva_get_icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<input class="rentiva-input" type="date" id="rentiva-search-return" name="return_date" value="<?php echo esc_attr( gmdate( 'Y-m-d', strtotime( '+3 days' ) ) ); ?>">
					</div>
				</div>
				<div class="rentiva-field rentiva-search-panel__field">
					<label class="rentiva-field__label" for="rentiva-search-category"><?php echo esc_html( $rentiva_field_category_label ); ?></label>
					<div class="rentiva-field__input-wrap">
						<span class="rentiva-field__icon"><?php echo rentiva_get_icon( 'list' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<select class="rentiva-select" id="rentiva-search-category" name="rbfw_item_caregory">
							<?php foreach ( $rentiva_categories as $rentiva_category ) : ?>
								<option value="<?php echo esc_attr( $rentiva_category['slug'] ); ?>"><?php echo esc_html( $rentiva_category['name'] ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<button type="submit" class="rentiva-search-panel__submit">
					<?php echo rentiva_get_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo esc_html( $rentiva_submit_text ); ?>
				</button>
			</div>
		</form>
	</div>
</section>
