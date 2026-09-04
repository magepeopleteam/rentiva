<?php
/**
 * Search results template.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<header class="rentiva-page-header rentiva-container">
	<h1 class="rentiva-h1 rentiva-page-header__title">
		<?php
		printf(
			/* translators: %s: search query */
			esc_html__( 'Search results for: %s', 'rentiva' ),
			'<span>' . esc_html( get_search_query() ) . '</span>'
		);
		?>
	</h1>
	<?php get_search_form(); ?>
</header>

<div class="rentiva-container rentiva-section">
	<?php if ( have_posts() ) : ?>
		<div class="rentiva-content-list">
			<?php
			while ( have_posts() ) :
				the_post();
				rentiva_template_part( 'template-parts/content/content' );
			endwhile;

			the_posts_pagination();
			?>
		</div>
	<?php else : ?>
		<?php rentiva_template_part( 'template-parts/content/content-none' ); ?>
	<?php endif; ?>
</div>

<?php
get_footer();
