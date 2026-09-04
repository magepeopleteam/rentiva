<?php
/**
 * "Explore What You Need" — 6-card category grid.
 *
 * Shows the mockup's 6 featured categories (Bicycles/Scooters/Camping/
 * Cameras/Water Sports/Outdoor Gear) by name when they exist as real
 * `rbfw_item_caregory` terms — see rentiva_get_homepage_categories() in
 * inc/template-functions.php, which the hero search dropdown also uses so
 * both stay in sync.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_cards = rentiva_get_homepage_categories();
?>
<section class="rentiva-section--lg rentiva-categories" id="categories">
	<div class="rentiva-container">
		<div class="rentiva-section-head">
			<div class="rentiva-section-head__title">
				<h2 class="rentiva-h2"><?php esc_html_e( 'Explore What You Need', 'rentiva' ); ?></h2>
				<p class="rentiva-lead"><?php esc_html_e( 'Find the right equipment for your next adventure.', 'rentiva' ); ?></p>
			</div>
			<a href="<?php echo esc_url( rentiva_get_rentals_page_url() ); ?>" class="rentiva-link">
				<?php esc_html_e( 'View all categories', 'rentiva' ); ?>
				<?php echo rentiva_get_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>

		<div class="rentiva-categories__grid">
			<?php foreach ( $rentiva_cards as $rentiva_card ) : ?>
				<?php rentiva_template_part( 'template-parts/cards/category-card', '', $rentiva_card ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
