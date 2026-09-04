<?php
/**
 * Generic post/page content teaser — used by index.php, search.php, blog
 * archives. Not part of the rental-item card system (see cards/rental-card.php).
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'rentiva-post-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="rentiva-post-card__thumb">
			<?php the_post_thumbnail( 'rentiva-card' ); ?>
		</a>
	<?php endif; ?>

	<div class="rentiva-post-card__body">
		<h2 class="rentiva-h4 rentiva-post-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<p class="rentiva-small rentiva-post-card__meta">
			<?php echo esc_html( get_the_date() ); ?>
		</p>

		<div class="rentiva-body rentiva-post-card__excerpt">
			<?php echo wp_kses_post( wpautop( rentiva_trim_words( get_the_excerpt(), 30 ) ) ); ?>
		</div>

		<a href="<?php the_permalink(); ?>" class="rentiva-link">
			<?php esc_html_e( 'Read more', 'rentiva' ); ?>
			<?php echo rentiva_get_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</div>
</article>
