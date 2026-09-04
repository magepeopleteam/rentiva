<?php
/**
 * 404 template.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="rentiva-container rentiva-404">
	<p class="rentiva-404__code">404</p>
	<h1 class="rentiva-h2"><?php esc_html_e( 'This page took a wrong turn.', 'rentiva' ); ?></h1>
	<p class="rentiva-lead" style="margin-block:1rem 2rem;">
		<?php esc_html_e( "The page you're looking for doesn't exist or has moved.", 'rentiva' ); ?>
	</p>

	<div class="rentiva-flex rentiva-flex--center rentiva-gap-2" style="justify-content:center; margin-bottom:2.5rem;">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--primary">
			<?php esc_html_e( 'Back to home', 'rentiva' ); ?>
		</a>
		<a href="<?php echo esc_url( rentiva_get_rentals_page_url() ); ?>" class="btn btn--outline">
			<?php esc_html_e( 'Browse rentals', 'rentiva' ); ?>
		</a>
	</div>

	<?php get_search_form(); ?>
</div>

<?php
get_footer();
