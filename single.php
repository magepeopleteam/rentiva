<?php
/**
 * Single blog post template (ordinary `post` content — not `rbfw_item`,
 * which is handled entirely by templates/single/single-rbfw.php).
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
			<?php if ( has_category() ) : ?>
				<p class="rentiva-eyebrow" style="margin-bottom:1rem;"><?php the_category( ', ' ); ?></p>
			<?php endif; ?>
			<h1 class="rentiva-h1 rentiva-page-header__title"><?php the_title(); ?></h1>
			<p class="rentiva-small">
				<?php
				printf(
					/* translators: 1: post date, 2: post author */
					esc_html__( '%1$s · by %2$s', 'rentiva' ),
					esc_html( get_the_date() ),
					esc_html( get_the_author() )
				);
				?>
			</p>
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
