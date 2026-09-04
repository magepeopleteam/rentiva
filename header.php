<?php
/**
 * The header for our theme.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text" href="#rentiva-content"><?php esc_html_e( 'Skip to content', 'rentiva' ); ?></a>

<?php do_action( 'rentiva_before_header' ); ?>
<?php rentiva_template_part( 'template-parts/header/site-header' ); ?>
<?php do_action( 'rentiva_after_header' ); ?>
<main id="rentiva-content" class="site-main">
