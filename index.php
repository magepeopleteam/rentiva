<?php
/**
 * The default template — fallback for any request type WordPress can't
 * otherwise match (also the classic-theme-required index.php).
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

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
