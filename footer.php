<?php
/**
 * The footer for our theme.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main><!-- #rentiva-content -->
<?php
do_action( 'rentiva_before_footer' );
rentiva_template_part( 'template-parts/footer/site-footer' );
do_action( 'rentiva_after_footer' );
?>
<?php wp_footer(); ?>
</body>
</html>
