<?php
/**
 * Site header markup — fixed, transparent-over-hero on the homepage,
 * matching mockup/src/components/Header.tsx.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header class="site-header" id="masthead">
	<div class="site-header__inner">
		<?php rentiva_the_logo(); ?>

		<nav class="site-header__nav" aria-label="<?php esc_attr_e( 'Primary', 'rentiva' ); ?>">
			<?php rentiva_primary_nav(); ?>
		</nav>

		<div class="site-header__actions">
			<a href="<?php echo esc_url( rentiva_get_signin_url() ); ?>" class="site-header__text-btn">
				<?php esc_html_e( 'Sign In', 'rentiva' ); ?>
			</a>
			<a href="<?php echo esc_url( rentiva_get_list_item_url() ); ?>" class="btn btn--primary">
				<?php esc_html_e( 'List Your Item', 'rentiva' ); ?>
			</a>
		</div>

		<button type="button" class="site-header__toggle" aria-expanded="false" aria-controls="rentiva-mobile-menu">
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'rentiva' ); ?></span>
			<span aria-hidden="true"></span>
			<span aria-hidden="true"></span>
			<span aria-hidden="true"></span>
		</button>
	</div>

	<div class="site-header__mobile-menu" id="rentiva-mobile-menu">
		<?php rentiva_primary_nav(); ?>
		<div class="site-header__mobile-actions">
			<a href="<?php echo esc_url( rentiva_get_signin_url() ); ?>" class="btn--ghost">
				<?php esc_html_e( 'Sign In', 'rentiva' ); ?>
			</a>
			<a href="<?php echo esc_url( rentiva_get_list_item_url() ); ?>" class="btn btn--primary">
				<?php esc_html_e( 'List Your Item', 'rentiva' ); ?>
			</a>
		</div>
	</div>
</header>
