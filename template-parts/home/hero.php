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

$rentiva_hero_image_id = (int) rentiva_get_setting( 'hero_image_id', 0 );
$rentiva_eyebrow       = rentiva_get_setting( 'hero_eyebrow', __( 'RENT • RIDE • EXPLORE', 'rentiva' ) );
$rentiva_title         = rentiva_get_setting( 'hero_title', __( 'Rent. Ride. Explore.', 'rentiva' ) );
$rentiva_subtitle      = rentiva_get_setting( 'hero_subtitle', __( 'Premium bikes, gear and equipment — ready whenever you are.', 'rentiva' ) );

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
				<span class="rentiva-hero__badge-val"><?php esc_html_e( '4.9 / 5', 'rentiva' ); ?></span>
				<span class="rentiva-hero__badge-lbl"><?php esc_html_e( 'Top Rated', 'rentiva' ); ?></span>
			</span>
		</div>
		<div class="rentiva-hero__badge-glass">
			<span class="rentiva-hero__badge-icon rentiva-hero__badge-icon--dark">
				<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="white" stroke-width="1.5"><rect x="2" y="7" width="12" height="7" rx="2"/><path d="M5 7V5a3 3 0 016 0v2"/></svg>
			</span>
			<span>
				<span class="rentiva-hero__badge-val"><?php esc_html_e( '10,000+', 'rentiva' ); ?></span>
				<span class="rentiva-hero__badge-lbl"><?php esc_html_e( 'Rentals Done', 'rentiva' ); ?></span>
			</span>
		</div>
		<div class="rentiva-hero__badge-live">
			<span class="rentiva-hero__pulse-dot" aria-hidden="true"></span>
			<span><?php esc_html_e( 'Available Now', 'rentiva' ); ?></span>
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
			<a href="<?php echo esc_url( rentiva_get_rentals_page_url() ); ?>" class="btn btn--primary btn--lg">
				<?php esc_html_e( 'Explore Rentals', 'rentiva' ); ?>
			</a>
			<a href="<?php echo esc_url( rentiva_get_list_item_url() ); ?>" class="btn btn--glass">
				<?php esc_html_e( 'List Your Equipment', 'rentiva' ); ?>
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
				<span class="rentiva-search-panel__label-text"><?php esc_html_e( 'FIND YOUR RENTAL', 'rentiva' ); ?></span>
			</div>
			<div class="rentiva-search-panel__grid">
				<div class="rentiva-field rentiva-search-panel__field">
					<label class="rentiva-field__label" for="rentiva-search-where"><?php esc_html_e( 'Where', 'rentiva' ); ?></label>
					<div class="rentiva-field__input-wrap">
						<span class="rentiva-field__icon"><?php echo rentiva_get_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<input class="rentiva-input" type="text" id="rentiva-search-where" name="location" placeholder="<?php esc_attr_e( 'Choose location', 'rentiva' ); ?>">
					</div>
				</div>
				<div class="rentiva-field rentiva-search-panel__field">
					<label class="rentiva-field__label" for="rentiva-search-pickup"><?php esc_html_e( 'Pickup', 'rentiva' ); ?></label>
					<div class="rentiva-field__input-wrap">
						<span class="rentiva-field__icon"><?php echo rentiva_get_icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<input class="rentiva-input" type="date" id="rentiva-search-pickup" name="pickup_date" value="<?php echo esc_attr( gmdate( 'Y-m-d', strtotime( '+1 day' ) ) ); ?>">
					</div>
				</div>
				<div class="rentiva-field rentiva-search-panel__field">
					<label class="rentiva-field__label" for="rentiva-search-return"><?php esc_html_e( 'Return', 'rentiva' ); ?></label>
					<div class="rentiva-field__input-wrap">
						<span class="rentiva-field__icon"><?php echo rentiva_get_icon( 'calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<input class="rentiva-input" type="date" id="rentiva-search-return" name="return_date" value="<?php echo esc_attr( gmdate( 'Y-m-d', strtotime( '+3 days' ) ) ); ?>">
					</div>
				</div>
				<div class="rentiva-field rentiva-search-panel__field">
					<label class="rentiva-field__label" for="rentiva-search-category"><?php esc_html_e( 'Category', 'rentiva' ); ?></label>
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
					<?php esc_html_e( 'Find Rentals', 'rentiva' ); ?>
				</button>
			</div>
		</form>
	</div>
</section>
