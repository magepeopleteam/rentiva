<?php
/**
 * Rentiva Setup — a first-run welcome notice, a persistent "Rentiva Setup"
 * dashboard widget, and the full "Rentiva → Setup" admin page (required-
 * plugin status, demo import, and homepage/next-step shortcuts). Elementor
 * is a hard theme dependency (style.css's `Requires Plugins`), so the
 * homepage is meant to be a real, fully editable Elementor page from the
 * first run — Setup exists to get a fresh install there in three steps.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flag that Rentiva was just activated, so the welcome notice shows once,
 * and auto-provision the Elementor-editable "Homepage" page if the site
 * doesn't already have a static front page of its own.
 *
 * @return void
 */
function rentiva_flag_activation() {
	set_transient( 'rentiva_show_welcome_notice', 1, DAY_IN_SECONDS );

	if ( rentiva_has_elementor() && 'page' !== get_option( 'show_on_front' ) ) {
		rentiva_setup_homepage_page();
	}
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

	$missing_plugins = wp_list_pluck( array_filter( rentiva_get_required_plugins(), fn( $p ) => ! $p['is_active'] ), 'label' );
	?>
	<div class="notice notice-success is-dismissible">
		<p><strong><?php esc_html_e( 'Welcome to Rentiva!', 'rentiva' ); ?></strong></p>
		<?php if ( ! empty( $missing_plugins ) ) : ?>
			<p>
				<?php
				printf(
					/* translators: %s: comma-separated list of missing required plugins */
					esc_html__( 'Rentiva requires: %s. Head to Rentiva → Setup to install and activate them.', 'rentiva' ),
					esc_html( implode( ', ', $missing_plugins ) )
				);
				?>
			</p>
		<?php endif; ?>
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=rentiva-settings' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Open Rentiva Setup', 'rentiva' ); ?>
			</a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'rentiva_welcome_notice' );

/**
 * Hide Booking and Rental Manager for WooCommerce's own
 * `RBFW_Admin_Payment_Notice` "no payment gateway enabled" warning on the
 * Rentiva → Setup screen only — Setup's own "Required plugins" card already
 * covers that plugin's status, so its competing notice on the very same
 * screen is just noise. Every other admin screen (including Rentiva →
 * Theme Settings) still shows it, since it's a real, actionable warning
 * everywhere else. Removed via `current_screen` (which fires well before
 * `admin_notices`) rather than editing the plugin, so a plugin update never
 * clobbers this.
 *
 * @return void
 */
function rentiva_hide_rbfw_payment_notice_on_setup_page() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check, no state change.
	if ( ! isset( $_GET['page'] ) || 'rentiva-settings' !== $_GET['page'] || ! class_exists( 'RBFW_Admin_Payment_Notice' ) ) {
		return;
	}

	global $wp_filter;
	if ( empty( $wp_filter['admin_notices'] ) ) {
		return;
	}

	foreach ( $wp_filter['admin_notices']->callbacks as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			$function = $callback['function'];
			if ( is_array( $function ) && isset( $function[0] ) && $function[0] instanceof RBFW_Admin_Payment_Notice ) {
				remove_action( 'admin_notices', $function, $priority );
			}
		}
	}
}
add_action( 'current_screen', 'rentiva_hide_rbfw_payment_notice_on_setup_page' );

/**
 * Handles the "Create editable Homepage page" action — creates (or reuses)
 * the "Homepage" page, sets it as the static front page, then sends the
 * admin straight into editing it (Elementor if active, else the block
 * editor).
 *
 * @return void
 */
function rentiva_handle_setup_homepage() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'rentiva' ) );
	}
	check_admin_referer( 'rentiva_setup_homepage' );

	$page_id = rentiva_setup_homepage_page();

	if ( ! $page_id ) {
		wp_safe_redirect( admin_url( 'index.php' ) );
		exit;
	}

	// The normal page-edit screen, not a direct deep link into the Elementor
	// editor: Elementor itself replaces that screen's editor with an
	// "Edit with Elementor" call-to-action for any page already in builder
	// mode, which is where the complete page design actually opens for
	// editing. Landing on the plain edit screen first also matches what
	// Pages → All Pages → Edit does, so the experience stays familiar.
	wp_safe_redirect( admin_url( 'post.php?post=' . $page_id . '&action=edit' ) );
	exit;
}
add_action( 'admin_post_rentiva_setup_homepage', 'rentiva_handle_setup_homepage' );

/**
 * Register the "Rentiva Setup" dashboard widget: a compact version of the
 * full Rentiva → Setup admin page, for a quick glance from Dashboard → Home.
 *
 * @return void
 */
function rentiva_register_setup_dashboard_widget() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	wp_add_dashboard_widget(
		'rentiva_setup',
		__( 'Rentiva Setup', 'rentiva' ),
		'rentiva_render_setup_dashboard_widget'
	);
}
add_action( 'wp_dashboard_setup', 'rentiva_register_setup_dashboard_widget' );

/**
 * The three plugins Rentiva depends on (see style.css's `Requires Plugins`
 * header), with the metadata the Setup page/widget needs to show status and
 * offer one-click install/activate.
 *
 * @return array<int,array{label:string,description:string,icon:string,slug:string,file:string,is_active:bool}>
 */
function rentiva_get_required_plugins() {
	return array(
		array(
			'label'       => __( 'Elementor', 'rentiva' ),
			'description' => __( 'Required — homepage & page builder', 'rentiva' ),
			'icon'        => 'dashicons-layout',
			'slug'        => 'elementor',
			'file'        => 'elementor/elementor.php',
			'is_active'   => rentiva_has_elementor(),
		),
		array(
			'label'       => __( 'Booking and Rental Manager for WooCommerce', 'rentiva' ),
			'description' => __( 'Required — rental items, pricing & booking', 'rentiva' ),
			'icon'        => 'dashicons-calendar-alt',
			'slug'        => 'booking-and-rental-manager-for-woocommerce',
			'file'        => 'booking-and-rental-manager-for-woocommerce/booking-and-rental-manager-for-woocommerce.php',
			'is_active'   => rentiva_has_booking_plugin(),
		),
		array(
			'label'       => __( 'WooCommerce', 'rentiva' ),
			'description' => __( 'Required — cart & checkout', 'rentiva' ),
			'icon'        => 'dashicons-cart',
			'slug'        => 'woocommerce',
			'file'        => 'woocommerce/woocommerce.php',
			'is_active'   => rentiva_has_woocommerce(),
		),
	);
}

/**
 * Print an "Install Now" or "Activate" button for one required plugin, or
 * nothing if it's already active (or the current user lacks the capability
 * for whichever action applies).
 *
 * @param array{label:string,slug:string,file:string,is_active:bool} $plugin
 * @return void
 */
function rentiva_render_plugin_action_button( $plugin ) {
	if ( $plugin['is_active'] ) {
		return;
	}

	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$is_installed = array_key_exists( $plugin['file'], get_plugins() );

	if ( $is_installed ) {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$url = wp_nonce_url(
			admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $plugin['file'] ) ),
			'activate-plugin_' . $plugin['file']
		);
		?>
		<a href="<?php echo esc_url( $url ); ?>" class="button button-small"><?php esc_html_e( 'Activate', 'rentiva' ); ?></a>
		<?php
		return;
	}

	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	$url = wp_nonce_url(
		admin_url( 'update.php?action=install-plugin&plugin=' . rawurlencode( $plugin['slug'] ) ),
		'install-plugin_' . $plugin['slug']
	);
	?>
	<a href="<?php echo esc_url( $url ); ?>" class="button button-small button-primary"><?php esc_html_e( 'Install Now', 'rentiva' ); ?></a>
	<?php
}

/**
 * Render the shared top bar (brand + Setup/Theme Settings tabs) both Rentiva
 * admin pages open with.
 *
 * @param string $active 'setup' or 'settings'.
 * @return void
 */
function rentiva_render_admin_topbar( $active ) {
	?>
	<div class="rentiva-admin-topbar">
		<div class="rentiva-admin-topbar__brand">
			<span class="dashicons dashicons-admin-customizer" aria-hidden="true"></span>
			<?php esc_html_e( 'Rentiva', 'rentiva' ); ?>
		</div>
		<nav class="rentiva-admin-topbar__nav">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=rentiva-settings' ) ); ?>" class="<?php echo 'setup' === $active ? 'is-active' : ''; ?>">
				<?php esc_html_e( 'Setup', 'rentiva' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=rentiva-theme-settings' ) ); ?>" class="<?php echo 'settings' === $active ? 'is-active' : ''; ?>">
				<?php esc_html_e( 'Theme Settings', 'rentiva' ); ?>
			</a>
		</nav>
	</div>
	<?php
}

/**
 * Render the compact "Rentiva Setup" dashboard widget — plugin status dots
 * and the two most common actions (import demo content, edit the homepage).
 * The full three-step walkthrough lives on the Rentiva → Setup admin page.
 *
 * @return void
 */
function rentiva_render_setup_dashboard_widget() {
	$plugins = rentiva_get_required_plugins();
	?>
	<ul style="margin-top:0;">
		<?php foreach ( $plugins as $plugin ) : ?>
			<li style="margin-bottom:6px;">
				<span style="color:<?php echo $plugin['is_active'] ? '#00a32a' : '#d63638'; ?>;">●</span>
				<?php echo esc_html( $plugin['label'] ); ?>
				&mdash;
				<?php echo $plugin['is_active'] ? esc_html__( 'Active', 'rentiva' ) : esc_html__( 'Not active', 'rentiva' ); ?>
				<?php rentiva_render_plugin_action_button( $plugin ); ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<p>
		<?php if ( rentiva_has_booking_plugin() ) : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;">
				<input type="hidden" name="action" value="rentiva_import_demo">
				<?php wp_nonce_field( 'rentiva_import_demo' ); ?>
				<button type="submit" class="button"><?php esc_html_e( 'Import demo content', 'rentiva' ); ?></button>
			</form>
		<?php endif; ?>
	</p>
	<p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=rentiva-settings' ) ); ?>"><?php esc_html_e( 'Open Rentiva Setup', 'rentiva' ); ?></a>
		&nbsp;|&nbsp;
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=rentiva-theme-settings' ) ); ?>"><?php esc_html_e( 'Open Theme Settings', 'rentiva' ); ?></a>
	</p>
	<?php
}

/**
 * Render the "Rentiva → Setup" admin page: three steps (required plugins,
 * import demo content, next steps) mirroring the site's own card design —
 * see assets/css/admin.css, which reuses assets/css/variables.css's tokens.
 *
 * @return void
 */
function rentiva_render_setup_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$plugins        = rentiva_get_required_plugins();
	$plugins_ready  = ! in_array( false, wp_list_pluck( $plugins, 'is_active' ), true );
	$imported_at    = get_option( 'rentiva_demo_imported_at' );
	$demo_imported  = ! empty( $imported_at );
	$homepage_ready = rentiva_homepage_uses_custom_builder();
	$front_id       = (int) get_option( 'page_on_front' );

	// The normal page-edit screen rather than a direct deep link into the
	// Elementor editor: for a page already in builder mode, Elementor
	// replaces that screen's editor with its own "Edit with Elementor"
	// call-to-action, which is where the complete page design opens.
	$homepage_edit_url = $homepage_ready
		? get_edit_post_link( $front_id, 'raw' )
		: wp_nonce_url( admin_url( 'admin-post.php?action=rentiva_setup_homepage' ), 'rentiva_setup_homepage' );
	?>
	<div class="wrap rentiva-admin">
		<?php rentiva_render_admin_topbar( 'setup' ); ?>

		<h1><?php esc_html_e( 'Get Rentiva ready', 'rentiva' ); ?></h1>
		<p class="rentiva-admin-intro"><?php esc_html_e( 'Install the required plugins, import the demo content, then jump into your live site — homepage and rentals wired up.', 'rentiva' ); ?></p>

		<div class="rentiva-steps">
			<div class="rentiva-step-chip<?php echo $plugins_ready ? ' is-done' : ''; ?>">
				<span class="rentiva-step-chip__check"><?php echo $plugins_ready ? '&#10003;' : '1'; ?></span>
				<?php esc_html_e( 'Plugins', 'rentiva' ); ?>
			</div>
			<div class="rentiva-step-chip<?php echo $demo_imported ? ' is-done' : ''; ?>">
				<span class="rentiva-step-chip__check"><?php echo $demo_imported ? '&#10003;' : '2'; ?></span>
				<?php esc_html_e( 'Import', 'rentiva' ); ?>
			</div>
			<div class="rentiva-step-chip<?php echo ( $plugins_ready && $homepage_ready ) ? ' is-done' : ''; ?>">
				<span class="rentiva-step-chip__check"><?php echo ( $plugins_ready && $homepage_ready ) ? '&#10003;' : '3'; ?></span>
				<?php esc_html_e( 'Explore', 'rentiva' ); ?>
			</div>
		</div>

		<div class="rentiva-card">
			<div class="rentiva-card__header">
				<div>
					<span class="rentiva-card__eyebrow"><?php esc_html_e( 'Step 1', 'rentiva' ); ?></span>
					<h2><?php esc_html_e( 'Required plugins', 'rentiva' ); ?></h2>
					<p><?php esc_html_e( 'Elementor powers the homepage builder, Booking and Rental Manager for WooCommerce powers rental items and booking, and WooCommerce powers checkout.', 'rentiva' ); ?></p>
				</div>
				<span class="rentiva-badge <?php echo $plugins_ready ? 'rentiva-badge--ready' : 'rentiva-badge--attention'; ?>">
					<?php echo $plugins_ready ? esc_html__( 'Ready', 'rentiva' ) : esc_html__( 'Needs attention', 'rentiva' ); ?>
				</span>
			</div>

			<?php foreach ( $plugins as $plugin ) : ?>
				<div class="rentiva-plugin-row">
					<span class="rentiva-plugin-row__icon dashicons <?php echo esc_attr( $plugin['icon'] ); ?>" aria-hidden="true"></span>
					<div class="rentiva-plugin-row__body">
						<strong><?php echo esc_html( $plugin['label'] ); ?></strong>
						<span><?php echo esc_html( $plugin['description'] ); ?></span>
					</div>
					<span class="rentiva-badge <?php echo $plugin['is_active'] ? 'rentiva-badge--ready' : 'rentiva-badge--attention'; ?>">
						<?php echo $plugin['is_active'] ? esc_html__( 'Active', 'rentiva' ) : esc_html__( 'Not active', 'rentiva' ); ?>
					</span>
					<?php rentiva_render_plugin_action_button( $plugin ); ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="rentiva-card">
			<div class="rentiva-card__header">
				<div>
					<span class="rentiva-card__eyebrow"><?php esc_html_e( 'Step 2', 'rentiva' ); ?></span>
					<h2><?php esc_html_e( 'Import demo content', 'rentiva' ); ?></h2>
					<p><?php esc_html_e( 'Rental categories, pickup locations, and sample rental items, so the homepage and archive pages aren\'t empty while you build out your real catalog.', 'rentiva' ); ?></p>
				</div>
				<span class="rentiva-badge <?php echo $demo_imported ? 'rentiva-badge--ready' : 'rentiva-badge--attention'; ?>">
					<?php echo $demo_imported ? esc_html__( 'Imported', 'rentiva' ) : esc_html__( 'Not imported', 'rentiva' ); ?>
				</span>
			</div>

			<?php if ( $demo_imported ) : ?>
				<div class="rentiva-notice rentiva-notice--success">
					<strong><?php esc_html_e( 'Demo already imported', 'rentiva' ); ?></strong>
					<p>
						<?php
						printf(
							/* translators: %s: date/time of last import */
							esc_html__( 'Imported on %s. Running again will not duplicate existing demo items.', 'rentiva' ),
							esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $imported_at ) )
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( rentiva_has_booking_plugin() ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="rentiva_import_demo">
					<?php wp_nonce_field( 'rentiva_import_demo' ); ?>
					<button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'Import Demo Content', 'rentiva' ); ?></button>
				</form>
			<?php else : ?>
				<p><em><?php esc_html_e( 'Install and activate Booking and Rental Manager for WooCommerce to import demo content.', 'rentiva' ); ?></em></p>
			<?php endif; ?>
		</div>

		<div class="rentiva-card">
			<div class="rentiva-card__header">
				<div>
					<span class="rentiva-card__eyebrow"><?php esc_html_e( 'Step 3', 'rentiva' ); ?></span>
					<h2><?php esc_html_e( 'Next steps', 'rentiva' ); ?></h2>
					<p><?php esc_html_e( 'Polish your brand, edit the homepage, and open the site your visitors will see.', 'rentiva' ); ?></p>
				</div>
			</div>

			<div class="rentiva-next-steps">
				<a class="rentiva-next-step-tile" href="<?php echo esc_url( $homepage_edit_url ); ?>">
					<span class="dashicons dashicons-edit" aria-hidden="true"></span>
					<strong><?php esc_html_e( 'Edit Homepage', 'rentiva' ); ?></strong>
					<span><?php esc_html_e( 'Opens the Homepage page — use "Edit with Elementor" there for the full design.', 'rentiva' ); ?></span>
				</a>
				<a class="rentiva-next-step-tile" href="<?php echo esc_url( admin_url( 'admin.php?page=rentiva-theme-settings' ) ); ?>">
					<span class="dashicons dashicons-admin-customizer" aria-hidden="true"></span>
					<strong><?php esc_html_e( 'Theme Settings', 'rentiva' ); ?></strong>
					<span><?php esc_html_e( 'Colors, hero copy, footer, and social links.', 'rentiva' ); ?></span>
				</a>
				<a class="rentiva-next-step-tile" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener noreferrer">
					<span class="dashicons dashicons-external" aria-hidden="true"></span>
					<strong><?php esc_html_e( 'View Homepage', 'rentiva' ); ?></strong>
					<span><?php esc_html_e( 'Preview the live front-end experience.', 'rentiva' ); ?></span>
				</a>
				<a class="rentiva-next-step-tile" href="<?php echo esc_url( rentiva_get_rentals_page_url() ); ?>" target="_blank" rel="noopener noreferrer">
					<span class="dashicons dashicons-grid-view" aria-hidden="true"></span>
					<strong><?php esc_html_e( 'View Rentals', 'rentiva' ); ?></strong>
					<span><?php esc_html_e( 'Browse the rentals archive page.', 'rentiva' ); ?></span>
				</a>
			</div>
		</div>
	</div>
	<?php
}
