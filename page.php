<?php
/**
 * Default page template.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="rentiva-page-header rentiva-container">
			<h1 class="rentiva-h1 rentiva-page-header__title"><?php the_title(); ?></h1>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="rentiva-container rentiva-container--content">
				<?php the_post_thumbnail( 'rentiva-hero', array( 'class' => 'rentiva-prose__thumb' ) ); ?>
			</div>
		<?php endif; ?>

		<div class="rentiva-container rentiva-section--tight">
			<div class="rentiva-prose">
				<?php the_content(); ?>
			</div>
		</div>
	</article>

	<?php
	if ( comments_open() || get_comments_number() ) :
		?>
		<div class="rentiva-container rentiva-container--narrow">
			<?php comments_template(); ?>
		</div>
		<?php
	endif;
endwhile;

get_footer();
