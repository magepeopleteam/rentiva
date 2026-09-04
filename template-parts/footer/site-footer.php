<?php
/**
 * Site footer — dark 5-column layout matching the mockup's homepage footer
 * section exactly (brand+socials spanning 2 cols, 3 link columns, copyright bar).
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rentiva_socials = rentiva_get_social_links();
$rentiva_year    = gmdate( 'Y' );
?>
<footer class="site-footer" id="colophon">
	<div class="rentiva-container">
		<div class="site-footer__grid">
			<div class="site-footer__brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-footer__logo">
					<span class="site-footer__mark" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 18 18" fill="none">
							<path d="M3 9C3 9 6 5 9 5C12 5 15 9 15 9C15 9 12 13 9 13C6 13 3 9 3 9Z" fill="white" fill-opacity="0.9"/>
							<circle cx="9" cy="9" r="2.5" fill="white"/>
						</svg>
					</span>
					<span class="site-footer__logo-text"><?php bloginfo( 'name' ); ?></span>
				</a>
				<p class="site-footer__tagline">
					<?php
					$rentiva_tagline = rentiva_get_setting( 'footer_tagline', __( "Rent smarter. Explore further.\nPremium gear from trusted local owners.", 'rentiva' ) );
					echo wp_kses( nl2br( esc_html( $rentiva_tagline ) ), array( 'br' => array() ) );
					?>
				</p>
				<div class="site-footer__socials">
					<?php foreach ( array( 'twitter', 'instagram', 'facebook' ) as $rentiva_platform ) : ?>
						<a href="<?php echo esc_url( $rentiva_socials[ $rentiva_platform ] ); ?>" class="site-footer__social" aria-label="<?php echo esc_attr( ucfirst( $rentiva_platform ) ); ?>">
							<?php echo rentiva_get_icon( $rentiva_platform ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside rentiva_get_icon(). ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="site-footer__column">
				<h5 class="site-footer__heading"><?php esc_html_e( 'Explore', 'rentiva' ); ?></h5>
				<?php rentiva_the_footer_nav( 'footer-explore' ); ?>
			</div>

			<div class="site-footer__column">
				<h5 class="site-footer__heading"><?php esc_html_e( 'Company', 'rentiva' ); ?></h5>
				<?php rentiva_the_footer_nav( 'footer-company' ); ?>
			</div>

			<div class="site-footer__column">
				<h5 class="site-footer__heading"><?php esc_html_e( 'Support', 'rentiva' ); ?></h5>
				<?php rentiva_the_footer_nav( 'footer-support' ); ?>
			</div>
		</div>

		<div class="site-footer__bottom">
			<p class="site-footer__copyright">
				<?php
				printf(
					/* translators: 1: current year, 2: site name */
					esc_html__( '© %1$s %2$s. All rights reserved.', 'rentiva' ),
					esc_html( $rentiva_year ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</p>
			<p class="site-footer__copyright">
				<?php esc_html_e( 'Designed with care for adventurers everywhere.', 'rentiva' ); ?>
			</p>
		</div>
	</div>
</footer>
