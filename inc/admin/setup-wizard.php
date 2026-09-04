<?php
/**
 * First-run welcome notice — points a fresh install at Theme Settings and
 * the one-click demo importer. Deliberately lightweight: a dismissible
 * admin notice rather than a multi-screen wizard, since there is nothing
 * Rentiva strictly requires before the site is usable (WooCommerce + the
 * booking plugin ship their own setup flows already).
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flag that Rentiva was just activated, so the welcome notice shows once.
 *
 * @return void
 */
function rentiva_flag_activation() {
	set_transient( 'rentiva_show_welcome_notice', 1, DAY_IN_SECONDS );
}
add_action( 'after_switch_theme', 'rentiva_flag_activation' );

/**
 * Show the welcome notice once, then clear the flag.
 *
 * @return void
 */
function rentiva_welcome_notice() {
	if ( ! current_user_can( 'edit_theme_options' ) || ! get_transient( 'rentiva_show_welcome_notice' ) ) {
		return;
	}
	delete_transient( 'rentiva_show_welcome_notice' );

	$missing_plugins = array();
	if ( ! rentiva_has_woocommerce() ) {
		$missing_plugins[] = 'WooCommerce';
	}
	if ( ! rentiva_has_booking_plugin() ) {
		$missing_plugins[] = 'Booking and Rental Manager for WooCommerce';
	}
	if ( ! rentiva_has_elementor() ) {
		$missing_plugins[] = 'Elementor';
	}
	?>
	<div class="notice notice-success is-dismissible">
		<p><strong><?php esc_html_e( 'Welcome to Rentiva!', 'rentiva' ); ?></strong></p>
		<?php if ( ! empty( $missing_plugins ) ) : ?>
			<p>
				<?php
				printf(
					/* translators: %s: comma-separated list of missing required plugins */
					esc_html__( 'Rentiva works best with: %s. Please install and activate them from the Plugins screen.', 'rentiva' ),
					esc_html( implode( ', ', $missing_plugins ) )
				);
				?>
			</p>
		<?php endif; ?>
		<p>
			<a href="<?php echo esc_url( admin_url( 'themes.php?page=rentiva-settings' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Open Rentiva Settings', 'rentiva' ); ?>
			</a>
			<?php if ( rentiva_has_booking_plugin() ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-left:6px;">
					<input type="hidden" name="action" value="rentiva_import_demo">
					<?php wp_nonce_field( 'rentiva_import_demo' ); ?>
					<button type="submit" class="button"><?php esc_html_e( 'Import demo content', 'rentiva' ); ?></button>
				</form>
			<?php endif; ?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'rentiva_welcome_notice' );
