<?php
/**
 * Comments template.
 *
 * On `rbfw_item` this doubles as the rental-item review list (see
 * inc/template-functions.php's rentiva_review_comment() callback and the
 * rentiva_rating comment-meta star field) — reviews live on the real CPT,
 * not the plugin's hidden shadow WooCommerce product. On every other post
 * type this renders ordinary threaded comments.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}

$rentiva_is_review = is_singular( 'rbfw_item' );
?>
<div class="rentiva-comments <?php echo $rentiva_is_review ? 'rentiva-reviews' : ''; ?>" id="comments">

	<?php if ( have_comments() ) : ?>
		<h2 class="rentiva-h4 rentiva-comments__title">
			<?php
			if ( $rentiva_is_review ) {
				esc_html_e( 'Reviews', 'rentiva' );
			} else {
				printf(
					/* translators: %s: number of comments */
					esc_html( _n( '%s comment', '%s comments', get_comments_number(), 'rentiva' ) ),
					esc_html( number_format_i18n( get_comments_number() ) )
				);
			}
			?>
		</h2>

		<ul class="rentiva-comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'callback'    => $rentiva_is_review ? 'rentiva_review_comment' : null,
					'avatar_size' => 48,
				)
			);
			?>
		</ul>

		<?php the_comments_pagination(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && ! $rentiva_is_review ) : ?>
		<p class="rentiva-small"><?php esc_html_e( 'Comments are closed.', 'rentiva' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'        => $rentiva_is_review ? __( 'Write a review', 'rentiva' ) : __( 'Leave a comment', 'rentiva' ),
			'label_submit'       => $rentiva_is_review ? __( 'Submit review', 'rentiva' ) : __( 'Post comment', 'rentiva' ),
			'comment_field'      => '<p class="comment-form-comment rentiva-field"><label for="comment" class="rentiva-field__label">' . ( $rentiva_is_review ? esc_html__( 'Your review', 'rentiva' ) : esc_html__( 'Comment', 'rentiva' ) ) . '</label><textarea id="comment" name="comment" class="rentiva-input" rows="5" required></textarea></p>',
			'class_submit'       => 'btn btn--primary',
		)
	);
	?>
</div>
