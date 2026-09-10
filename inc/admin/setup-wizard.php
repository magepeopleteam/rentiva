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
 * and run full auto-provisioning (see rentiva_maybe_auto_provision_demo()
 * below) in case both required plugins already happen to be active at
 * theme-activation time.
 *
 * @return void
 */
function rentiva_flag_activation() {
	set_transient( 'rentiva_show_welcome_notice', 1, DAY_IN_SECONDS );
	rentiva_maybe_auto_provision_demo();
}
add_action( 'after_switch_theme', 'rentiva_flag_activation' );

/**
 * Makes a fresh install look fully ready with zero manual clicks: the
 * Elementor-editable "Homepage" page (if the site has no static front page
 * of its own yet) and the full demo catalog — categories, pickup locations,
 * rental items, nav menus, and the Hero/Promo/Why/Testimonial homepage
 * images (rentiva_import_demo_content(), inc/demo-import/importer.php).
 *
 * Both halves need a plugin the theme doesn't control the activation order
 * of — Elementor for the homepage, the booking plugin for the catalog — so
 * this runs from two trigger points: theme activation (in case the plugins
 * are already active) and every plugin activation (in case the theme was
 * already active first, e.g. after a reset, which is normally the case).
 * Both underlying steps are idempotent — rentiva_setup_homepage_page() reuses
 * an existing "Homepage" page rather than duplicating it, and
 * rentiva_import_demo_content() skips anything that already exists by title
 * — so running this more than once, or on a site an admin has already
 * customized, never overwrites or duplicates anything.
 *
 * @return void
 */
function rentiva_maybe_auto_provision_demo() {
	if ( ! rentiva_has_elementor() || ! rentiva_has_booking_plugin() ) {
		return;
	}

	if ( 'page' !== get_option( 'show_on_front' ) ) {
		rentiva_setup_homepage_page();
	}

	if ( ! get_option( 'rentiva_demo_imported_at' ) ) {
		rentiva_import_demo_content();
	}
}
add_action( 'activated_plugin', 'rentiva_maybe_auto_provision_demo' );

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

	$missing_plugins = wp_list_pluck( array_filter( rentiva_get_required_plugins(), fn( $p ) => $p['required'] && ! $p['is_active'] ), 'label' );
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
 * `RBFW_Admin_Payment_Notice` "no payment gateway enabled" warning on
 * Rentiva's own two admin screens (Setup and Theme Settings) — Setup's own
 * "Required plugins" card already covers that plugin's status, so its
 * competing notice on either of Rentiva's own screens is just noise. Every
 * other admin screen still shows it, since it's a real, actionable warning
 * everywhere else. Removed via `current_screen` (which fires well before
 * `admin_notices`) rather than editing the plugin, so a plugin update never
 * clobbers this.
 *
 * @return void
 */
function rentiva_hide_rbfw_payment_notice_on_setup_page() {
	$rentiva_pages = array( 'rentiva-settings', 'rentiva-theme-settings' );
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check, no state change.
	if ( ! isset( $_GET['page'] ) || ! in_array( $_GET['page'], $rentiva_pages, true ) || ! class_exists( 'RBFW_Admin_Payment_Notice' ) ) {
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
 * Elementor, the booking plugin, and WooCommerce each queue their own
 * "welcome" redirect for the next admin page load when activated — a
 * transient set inside their own activation hook and consumed on a later
 * `admin_init`:
 *  - Elementor:       `elementor_activation_redirect` → `admin.php?page=elementor-app#onboarding`
 *  - Booking plugin:  `rbfw_plugin_activated` → `edit.php?post_type=rbfw_item`
 *  - WooCommerce:     `_wc_activation_redirect` → the WC setup wizard
 * Rentiva's own Setup page is meant to be the one onboarding flow for all
 * three, so left alone this hijacks the very page load meant to land back
 * on Setup — including Step 1's Install & Activate All flow, which reloads
 * the page once every plugin is active. Worse, the booking plugin's check
 * doesn't skip AJAX requests the way Elementor's does, so a stale transient
 * can trigger a mid-AJAX redirect and corrupt the JSON response `wp.updates`
 * is expecting, which is what makes Step 1's Activating… step look stuck.
 *
 * Cleared in two layers rather than editing any plugin file (an update
 * would just clobber that): immediately after Rentiva activates one of its
 * three companion plugins, and defensively on every AJAX request / load of
 * Rentiva's own admin pages, in case a transient was left over from an
 * earlier interrupted attempt.
 *
 * @return void
 */
function rentiva_clear_companion_activation_redirects() {
	delete_transient( 'elementor_activation_redirect' );
	delete_transient( 'rbfw_plugin_activated' );
	delete_transient( '_wc_activation_redirect' );
}

add_action(
	'activated_plugin',
	function ( $plugin ) {
		if ( in_array( $plugin, wp_list_pluck( rentiva_get_required_plugins(), 'file' ), true ) ) {
			rentiva_clear_companion_activation_redirects();
		}
	},
	999
);

add_action(
	'admin_init',
	function () {
		$rentiva_pages = array( 'rentiva-settings', 'rentiva-theme-settings' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen/request-type check, no state change.
		$on_rentiva_page = isset( $_GET['page'] ) && in_array( $_GET['page'], $rentiva_pages, true );
		if ( wp_doing_ajax() || $on_rentiva_page ) {
			rentiva_clear_companion_activation_redirects();
		}
	},
	0
);

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
 * The plugins Rentiva integrates with (see style.css's `Requires Plugins`
 * header for the two hard dependencies), with the metadata the Setup
 * page/widget needs to show status and offer one-click install/activate.
 *
 * WooCommerce is listed but not `required` — Booking and Rental Manager for
 * WooCommerce has its own native checkout, so WooCommerce is only needed if
 * the site owner wants its cart/checkout flow instead.
 *
 * `wporg` marks whether the slug is installable straight from the
 * WordPress.org repository via the core AJAX installer — Elementor and
 * WooCommerce are; the booking plugin is a premium download and never will
 * be, so its install button always falls back to manual instructions.
 *
 * @return array<int,array{label:string,description:string,icon:string,slug:string,file:string,is_active:bool,required:bool,wporg:bool}>
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
			'required'    => true,
			'wporg'       => true,
		),
		array(
			'label'       => __( 'Booking and Rental Manager for WooCommerce', 'rentiva' ),
			'description' => __( 'Required — rental items, pricing & booking', 'rentiva' ),
			'icon'        => 'dashicons-calendar-alt',
			'slug'        => 'booking-and-rental-manager-for-woocommerce',
			'file'        => 'booking-and-rental-manager-for-woocommerce/rent-manager.php',
			'is_active'   => rentiva_has_booking_plugin(),
			'required'    => true,
			'wporg'       => false,
		),
		array(
			'label'       => __( 'WooCommerce', 'rentiva' ),
			'description' => __( 'Optional — only needed if you use WooCommerce cart & checkout', 'rentiva' ),
			'icon'        => 'dashicons-cart',
			'slug'        => 'woocommerce',
			'file'        => 'woocommerce/woocommerce.php',
			'is_active'   => rentiva_has_woocommerce(),
			'required'    => false,
			'wporg'       => true,
		),
	);
}

/**
 * Print an "Install Now" or "Activate" button for one required plugin, or
 * nothing if it's already active (or the current user lacks the capability
 * for whichever action applies).
 *
 * The button is a real link to core's normal (non-JS) install/activate
 * handler — a safe fallback if JS fails to load — but carries the data-*
 * attributes assets/js/admin-setup.js needs to instead drive it through
 * `wp.updates.installPlugin()` / `activatePlugin()`, WordPress's own
 * AJAX installer (used by the Add Plugins/Plugins screens): each plugin is
 * one short, independent request queued through `wp.updates.queue` rather
 * than one long blocking call, so installing several plugins back to back
 * can't itself trip a PHP execution-time limit, and progress can be shown
 * per plugin as each request resolves.
 *
 * @param array{label:string,slug:string,file:string,is_active:bool,required:bool,wporg:bool} $plugin
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
	$data_attrs   = sprintf(
		' data-slug="%1$s" data-plugin-file="%2$s" data-name="%3$s" data-wporg="%4$s"',
		esc_attr( $plugin['slug'] ),
		esc_attr( $plugin['file'] ),
		esc_attr( $plugin['label'] ),
		$plugin['wporg'] ? '1' : '0'
	);

	if ( $is_installed ) {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		$url = wp_nonce_url(
			admin_url( 'plugins.php?action=activate&plugin=' . rawurlencode( $plugin['file'] ) ),
			'activate-plugin_' . $plugin['file']
		);
		?>
		<a href="<?php echo esc_url( $url ); ?>" class="button button-small rentiva-plugin-action rentiva-plugin-action--activate" data-task="activate"<?php echo $data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_attr() above. ?>>
			<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
			<?php esc_html_e( 'Activate', 'rentiva' ); ?>
		</a>
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
	<a href="<?php echo esc_url( $url ); ?>" class="button button-small button-primary rentiva-plugin-action rentiva-plugin-action--install" data-task="install"<?php echo $data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_attr() above. ?>>
		<span class="dashicons dashicons-download" aria-hidden="true"></span>
		<?php esc_html_e( 'Install Now', 'rentiva' ); ?>
	</a>
	<?php
}

/**
 * Whether every *required* plugin (Step 1 on Rentiva → Setup) is active yet
 * — the gate that unlocks Theme Settings. Optional plugins (e.g. WooCommerce)
 * don't factor in.
 *
 * @return bool
 */
function rentiva_required_plugins_ready() {
	$required = array_filter( rentiva_get_required_plugins(), fn( $p ) => $p['required'] );
	return ! in_array( false, wp_list_pluck( $required, 'is_active' ), true );
}

/**
 * Render the shared top bar (brand + Setup/Theme Settings tabs) both Rentiva
 * admin pages open with. The Theme Settings tab is locked (still visible,
 * but not a link) until Setup's Step 1 "Required plugins" is complete.
 *
 * @param string $active 'setup' or 'settings'.
 * @return void
 */
function rentiva_render_admin_topbar( $active ) {
	$plugins_ready = rentiva_required_plugins_ready();
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
			<?php if ( $plugins_ready ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=rentiva-theme-settings' ) ); ?>" class="<?php echo 'settings' === $active ? 'is-active' : ''; ?>">
					<?php esc_html_e( 'Theme Settings', 'rentiva' ); ?>
				</a>
			<?php else : ?>
				<span class="is-locked" title="<?php esc_attr_e( 'Complete Step 1 (Required plugins) on Setup first', 'rentiva' ); ?>">
					<span class="dashicons dashicons-lock" aria-hidden="true"></span>
					<?php esc_html_e( 'Theme Settings', 'rentiva' ); ?>
				</span>
			<?php endif; ?>
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
			<?php
			$dot_color = $plugin['is_active'] ? '#00a32a' : ( $plugin['required'] ? '#d63638' : '#7a7670' );
			$status    = $plugin['is_active'] ? esc_html__( 'Active', 'rentiva' ) : ( $plugin['required'] ? esc_html__( 'Not active', 'rentiva' ) : esc_html__( 'Optional', 'rentiva' ) );
			?>
			<li style="margin-bottom:6px;">
				<span style="color:<?php echo esc_attr( $dot_color ); ?>;">●</span>
				<?php echo esc_html( $plugin['label'] ); ?>
				&mdash;
				<?php echo $status; ?>
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
	$plugins_ready  = rentiva_required_plugins_ready();
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
					<p><?php esc_html_e( 'Elementor powers the homepage builder and Booking and Rental Manager for WooCommerce powers rental items, pricing and booking. WooCommerce is optional — only needed if you use its cart & checkout instead of the plugin\'s own native checkout.', 'rentiva' ); ?></p>
				</div>
				<span class="rentiva-badge <?php echo $plugins_ready ? 'rentiva-badge--ready' : 'rentiva-badge--attention'; ?>">
					<?php echo $plugins_ready ? esc_html__( 'Ready', 'rentiva' ) : esc_html__( 'Needs attention', 'rentiva' ); ?>
				</span>
			</div>

			<div class="rentiva-plugin-progress" hidden>
				<div class="rentiva-plugin-progress__bar"><span></span></div>
				<p class="rentiva-plugin-progress__text"></p>
			</div>

			<?php foreach ( $plugins as $plugin ) : ?>
				<?php
				if ( $plugin['is_active'] ) {
					$badge_class = 'rentiva-badge--ready';
					$badge_text  = esc_html__( 'Active', 'rentiva' );
				} elseif ( $plugin['required'] ) {
					$badge_class = 'rentiva-badge--attention';
					$badge_text  = esc_html__( 'Not active', 'rentiva' );
				} else {
					$badge_class = 'rentiva-badge--neutral';
					$badge_text  = esc_html__( 'Optional', 'rentiva' );
				}
				?>
				<div class="rentiva-plugin-row" data-plugin-row data-slug="<?php echo esc_attr( $plugin['slug'] ); ?>" data-required="<?php echo $plugin['required'] ? '1' : '0'; ?>">
					<span class="rentiva-plugin-row__icon dashicons <?php echo esc_attr( $plugin['icon'] ); ?>" aria-hidden="true"></span>
					<div class="rentiva-plugin-row__body">
						<strong><?php echo esc_html( $plugin['label'] ); ?></strong>
						<span><?php echo esc_html( $plugin['description'] ); ?></span>
					</div>
					<span class="rentiva-badge <?php echo esc_attr( $badge_class ); ?>" data-role="status-badge">
						<?php echo $badge_text; ?>
					</span>
					<span class="rentiva-plugin-row__action">
						<?php rentiva_render_plugin_action_button( $plugin ); ?>
					</span>
					<div class="rentiva-plugin-row__notice" hidden></div>
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
					<button type="submit" class="button button-primary button-hero rentiva-import-demo-btn">
						<span class="dashicons dashicons-database-import" aria-hidden="true"></span>
						<?php esc_html_e( 'Import Demo Content', 'rentiva' ); ?>
					</button>
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
				<?php if ( ! $plugins_ready ) : ?>
					<span class="rentiva-badge rentiva-badge--attention">
						<?php esc_html_e( 'Locked', 'rentiva' ); ?>
					</span>
				<?php endif; ?>
			</div>

			<?php
			$next_steps = array(
				array(
					'icon'  => 'dashicons-edit',
					'label' => __( 'Edit Homepage', 'rentiva' ),
					'desc'  => __( 'Opens the Homepage page — use "Edit with Elementor" there for the full design.', 'rentiva' ),
					'url'   => $homepage_edit_url,
					'blank' => false,
				),
				array(
					'icon'  => 'dashicons-admin-customizer',
					'label' => __( 'Theme Settings', 'rentiva' ),
					'desc'  => __( 'Colors, hero copy, footer, and social links.', 'rentiva' ),
					'url'   => admin_url( 'admin.php?page=rentiva-theme-settings' ),
					'blank' => false,
				),
				array(
					'icon'  => 'dashicons-external',
					'label' => __( 'View Homepage', 'rentiva' ),
					'desc'  => __( 'Preview the live front-end experience.', 'rentiva' ),
					'url'   => home_url( '/' ),
					'blank' => true,
				),
				array(
					'icon'  => 'dashicons-grid-view',
					'label' => __( 'View Rentals', 'rentiva' ),
					'desc'  => __( 'Browse the rentals archive page.', 'rentiva' ),
					'url'   => rentiva_get_rentals_page_url(),
					'blank' => true,
				),
			);
			?>

			<div class="rentiva-next-steps">
				<?php foreach ( $next_steps as $step ) : ?>
					<?php if ( $plugins_ready ) : ?>
						<a class="rentiva-next-step-tile" href="<?php echo esc_url( $step['url'] ); ?>" <?php echo $step['blank'] ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
							<span class="dashicons <?php echo esc_attr( $step['icon'] ); ?>" aria-hidden="true"></span>
							<strong><?php echo esc_html( $step['label'] ); ?></strong>
							<span><?php echo esc_html( $step['desc'] ); ?></span>
						</a>
					<?php else : ?>
						<span class="rentiva-next-step-tile is-locked" title="<?php esc_attr_e( 'Complete Step 1 (Required plugins) first', 'rentiva' ); ?>">
							<span class="dashicons dashicons-lock" aria-hidden="true"></span>
							<strong><?php echo esc_html( $step['label'] ); ?></strong>
							<span><?php esc_html_e( 'Locked until Step 1 (Required plugins) is complete.', 'rentiva' ); ?></span>
						</span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}
