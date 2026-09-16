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
 * Flag that Rentiva was just activated, so the next admin screen load opens
 * the Setup wizard (and the welcome notice shows once if that redirect
 * doesn't happen), and run full auto-provisioning (see
 * rentiva_maybe_auto_provision_demo() below) in case both required plugins
 * already happen to be active at theme-activation time.
 *
 * @return void
 */
function rentiva_flag_activation() {
	set_transient( 'rentiva_show_welcome_notice', 1, DAY_IN_SECONDS );
	set_transient( 'rentiva_activation_redirect', 1, MINUTE_IN_SECONDS );
	rentiva_maybe_auto_provision_demo();
}
add_action( 'after_switch_theme', 'rentiva_flag_activation' );

/**
 * Send the admin straight to Step 1 of Rentiva → Setup right after the theme
 * is activated. `after_switch_theme` fires from `init` (priority 99) on the
 * first request after the switch, so on a normal Appearance → Themes
 * activation this runs on that same request, before any output.
 *
 * Requests that can't (or shouldn't) be redirected — AJAX, cron, admin-post
 * form handlers, network admin, or a user who can't open Setup — leave the
 * flag in place for the next real admin screen load instead of consuming it.
 *
 * @return void
 */
function rentiva_redirect_to_setup_after_activation() {
	if ( ! get_transient( 'rentiva_activation_redirect' ) ) {
		return;
	}

	global $pagenow;
	if ( wp_doing_ajax() || wp_doing_cron() || 'admin-post.php' === $pagenow || is_network_admin() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	delete_transient( 'rentiva_activation_redirect' );

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check, no state change.
	if ( isset( $_GET['page'] ) && 'rentiva-settings' === $_GET['page'] ) {
		return;
	}

	// Setup is the one onboarding flow: a companion plugin activated moments
	// earlier mustn't send the admin to its own welcome screen instead.
	rentiva_clear_companion_activation_redirects();

	wp_safe_redirect( rentiva_get_setup_step_url( 'plugins' ) );
	exit;
}
// Priority 1: ahead of the companion plugins' own admin_init (10) redirects.
add_action( 'admin_init', 'rentiva_redirect_to_setup_after_activation', 1 );

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

	// rentiva_demo_import_ready(), not just the plugin check above: under
	// WP-CLI the booking plugin registers its post type but not its
	// taxonomies, and importing there would create every item uncategorized.
	if ( ! get_option( 'rentiva_demo_imported_at' ) && rentiva_demo_import_ready() ) {
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

	// Activation normally lands straight on the Setup wizard, whose Step 1
	// already covers everything this notice says.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check, no state change.
	if ( isset( $_GET['page'] ) && 'rentiva-settings' === $_GET['page'] ) {
		return;
	}

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
 * Booking and Rental Manager Pro and WooCommerce are listed but not
 * `required` — Pro is a paid add-on on top of the free booking plugin, and the
 * free plugin has its own native checkout, so WooCommerce is only needed if
 * the site owner wants its cart/checkout flow instead.
 *
 * `wporg` marks whether the slug is installable straight from the
 * WordPress.org repository via the core AJAX installer — Elementor, the free
 * booking plugin and WooCommerce are. Pro is a premium download that never
 * will be, so it's installed by uploading its zip instead
 * (rentiva_ajax_upload_plugin()).
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
			'description' => __( 'Required — free, rental items, pricing & booking', 'rentiva' ),
			'icon'        => 'dashicons-calendar-alt',
			'slug'        => 'booking-and-rental-manager-for-woocommerce',
			'file'        => 'booking-and-rental-manager-for-woocommerce/rent-manager.php',
			'is_active'   => rentiva_has_booking_plugin(),
			'required'    => true,
			'wporg'       => true,
		),
		array(
			'label'       => __( 'Booking and Rental Manager Pro', 'rentiva' ),
			'description' => __( 'Optional — premium add-on; upload the zip you received with your purchase', 'rentiva' ),
			'icon'        => 'dashicons-star-filled',
			'slug'        => 'booking-and-rental-manager-for-woocommerce-pro',
			'file'        => 'booking-and-rental-manager-for-woocommerce-pro/rent-pro.php',
			// The plugin's own activation state, not a runtime class check:
			// Pro loads nothing at all while the free plugin is inactive, so a
			// runtime check would keep offering Activate for an active plugin.
			'is_active'   => rentiva_is_plugin_file_active( 'booking-and-rental-manager-for-woocommerce-pro/rent-pro.php' ),
			'required'    => false,
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
 * Whether a plugin file is active on this site.
 *
 * @param string $file Plugin file relative to the plugins directory.
 * @return bool
 */
function rentiva_is_plugin_file_active( $file ) {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	return is_plugin_active( $file );
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

	if ( ! $plugin['wporg'] ) {
		// Premium plugin: nothing to download, so "install" means uploading
		// the zip the customer received. Without JS this is a plain link to
		// core's own Upload Plugin screen; admin-setup.js instead opens the
		// file picker and posts the zip to rentiva_ajax_upload_plugin().
		if ( ! current_user_can( 'upload_plugins' ) ) {
			return;
		}
		?>
		<a href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=upload' ) ); ?>" class="button button-small button-primary rentiva-plugin-action rentiva-plugin-action--install" data-task="upload"<?php echo $data_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_attr() above. ?>>
			<span class="dashicons dashicons-upload" aria-hidden="true"></span>
			<?php esc_html_e( 'Upload & Install', 'rentiva' ); ?>
		</a>
		<input type="file" class="rentiva-plugin-upload-input" accept=".zip,application/zip" hidden>
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
 * AJAX: install a premium plugin (one on rentiva_get_required_plugins() with
 * `wporg` false) from its uploaded zip — Step 1's "Upload & Install" button in
 * assets/js/admin-setup.js. Runs the zip through core's own Plugin_Upgrader,
 * the same code path as Plugins → Add New → Upload Plugin, and answers in the
 * same JSON shape core's `install-plugin` AJAX action does, so the row's
 * error notice reads the same either way.
 *
 * @return void
 */
function rentiva_ajax_upload_plugin() {
	check_ajax_referer( 'rentiva_upload_plugin' );

	$slug   = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
	$status = array(
		'install' => 'plugin',
		'slug'    => $slug,
	);

	if ( ! current_user_can( 'upload_plugins' ) ) {
		$status['errorMessage'] = __( 'Sorry, you are not allowed to upload plugins on this site.', 'rentiva' );
		wp_send_json_error( $status, 403 );
	}

	$plugin = null;
	foreach ( rentiva_get_required_plugins() as $candidate ) {
		if ( $candidate['slug'] === $slug && ! $candidate['wporg'] ) {
			$plugin = $candidate;
			break;
		}
	}

	if ( ! $plugin ) {
		$status['errorMessage'] = __( 'This plugin can\'t be uploaded from Rentiva Setup.', 'rentiva' );
		wp_send_json_error( $status );
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- raw upload array; every field used is validated below.
	$zip = isset( $_FILES['pluginzip'] ) && is_array( $_FILES['pluginzip'] ) ? $_FILES['pluginzip'] : array();

	if ( empty( $zip['tmp_name'] ) || ! is_string( $zip['tmp_name'] ) || ! empty( $zip['error'] ) || ! is_uploaded_file( $zip['tmp_name'] ) ) {
		$status['errorMessage'] = sprintf(
			/* translators: %s: maximum upload file size, e.g. "64 MB". */
			__( 'The zip file didn\'t upload. Make sure it\'s smaller than %s, the maximum upload size on this site.', 'rentiva' ),
			size_format( wp_max_upload_size() )
		);
		wp_send_json_error( $status );
	}

	$filetype = wp_check_filetype( isset( $zip['name'] ) && is_string( $zip['name'] ) ? $zip['name'] : '', array( 'zip' => 'application/zip' ) );
	if ( 'zip' !== $filetype['ext'] ) {
		$status['errorMessage'] = __( 'Choose the plugin\'s .zip file.', 'rentiva' );
		wp_send_json_error( $status );
	}

	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$status['pluginName'] = $plugin['label'];

	// Priority 9: before Plugin_Upgrader::check_package() (10) inspects the folder.
	$check_source = static function ( $source, $remote_source ) use ( $plugin ) {
		return rentiva_upload_plugin_source_selection( $source, $remote_source, $plugin );
	};
	add_filter( 'upgrader_source_selection', $check_source, 9, 2 );

	$skin     = new WP_Ajax_Upgrader_Skin();
	$upgrader = new Plugin_Upgrader( $skin );
	$result   = $upgrader->install( $zip['tmp_name'] );

	remove_filter( 'upgrader_source_selection', $check_source, 9 );

	if ( is_wp_error( $result ) ) {
		$status['errorCode']    = $result->get_error_code();
		$status['errorMessage'] = $result->get_error_message();
		wp_send_json_error( $status );
	} elseif ( is_wp_error( $skin->result ) ) {
		$status['errorCode']    = $skin->result->get_error_code();
		$status['errorMessage'] = $skin->result->get_error_message();
		wp_send_json_error( $status );
	} elseif ( $skin->get_errors()->has_errors() ) {
		$status['errorMessage'] = $skin->get_error_messages();
		wp_send_json_error( $status );
	} elseif ( ! $result ) {
		global $wp_filesystem;

		$status['errorCode']    = 'unable_to_connect_to_filesystem';
		$status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'rentiva' );
		if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors() ) {
			$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
		}
		wp_send_json_error( $status );
	}

	if ( ! array_key_exists( $plugin['file'], get_plugins() ) ) {
		$status['errorMessage'] = __( 'The zip was installed, but WordPress can\'t find the plugin in it.', 'rentiva' );
		wp_send_json_error( $status );
	}

	$status['plugin'] = $plugin['file'];
	wp_send_json_success( $status );
}
add_action( 'wp_ajax_rentiva_upload_plugin', 'rentiva_ajax_upload_plugin' );

/**
 * `upgrader_source_selection` callback for rentiva_ajax_upload_plugin():
 * refuses a zip that doesn't hold the expected plugin, and renames its
 * top-level folder to the plugin's real folder when it differs — a GitHub
 * "Download ZIP" unpacks to `{repo}-main/`, which would otherwise install the
 * plugin somewhere Setup never looks for it.
 *
 * @param string|WP_Error $source        Folder the zip's contents unpacked to.
 * @param string          $remote_source Working directory the zip was unpacked into.
 * @param array           $plugin        Entry from rentiva_get_required_plugins().
 * @return string|WP_Error
 */
function rentiva_upload_plugin_source_selection( $source, $remote_source, $plugin ) {
	global $wp_filesystem;

	if ( is_wp_error( $source ) ) {
		return $source;
	}

	$folder    = dirname( $plugin['file'] );
	$main_file = basename( $plugin['file'] );
	$source    = trailingslashit( $source );

	// A zip with no top-level folder unpacks straight into the working
	// directory — refused too, since there's no plugin folder to name.
	if ( trailingslashit( $remote_source ) === $source || ! $wp_filesystem->exists( $source . $main_file ) ) {
		return new WP_Error(
			'rentiva_wrong_plugin_zip',
			sprintf(
				/* translators: %s: plugin name. */
				__( 'This zip doesn\'t contain %s. Upload the plugin zip you received for it.', 'rentiva' ),
				$plugin['label']
			)
		);
	}

	if ( basename( untrailingslashit( $source ) ) === $folder ) {
		return $source;
	}

	$target = trailingslashit( $remote_source ) . $folder;
	if ( ! $wp_filesystem->move( untrailingslashit( $source ), $target ) ) {
		return new WP_Error(
			'rentiva_rename_failed',
			sprintf(
				/* translators: %s: plugin folder name. */
				__( 'Couldn\'t rename the zip\'s folder to %s.', 'rentiva' ),
				$folder
			)
		);
	}

	return trailingslashit( $target );
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
 * The Setup wizard's steps, in order — `step` query-arg key => chip label.
 *
 * @return array<string,string>
 */
function rentiva_get_setup_steps() {
	return array(
		'plugins' => __( 'Plugins', 'rentiva' ),
		'demo'    => __( 'Import', 'rentiva' ),
		'finish'  => __( 'Explore', 'rentiva' ),
	);
}

/**
 * URL of one Setup wizard step.
 *
 * @param string $step A key from rentiva_get_setup_steps().
 * @return string
 */
function rentiva_get_setup_step_url( $step ) {
	return add_query_arg( 'step', $step, admin_url( 'admin.php?page=rentiva-settings' ) );
}

/**
 * The step to show: the requested `?step=`, or the first step when it's
 * missing or unknown. Every step after the first stays locked until the
 * required plugins are active — the same Step 1 gate Theme Settings uses —
 * so a direct link can't skip past it.
 *
 * @return string
 */
function rentiva_get_current_setup_step() {
	$step_keys = array_keys( rentiva_get_setup_steps() );
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only navigation, no state change.
	$requested = isset( $_GET['step'] ) ? sanitize_key( wp_unslash( $_GET['step'] ) ) : '';

	if ( ! in_array( $requested, $step_keys, true ) || ! rentiva_required_plugins_ready() ) {
		return $step_keys[0];
	}

	return $requested;
}

/**
 * "Step 2 of 3" label for a step's card eyebrow.
 *
 * @param string $step A key from rentiva_get_setup_steps().
 * @return string
 */
function rentiva_get_setup_step_position_label( $step ) {
	$step_keys = array_keys( rentiva_get_setup_steps() );

	return sprintf(
		/* translators: 1: current step number, 2: total number of steps. */
		__( 'Step %1$d of %2$d', 'rentiva' ),
		(int) array_search( $step, $step_keys, true ) + 1,
		count( $step_keys )
	);
}

/**
 * Render the "Rentiva → Setup" admin page as a step-by-step wizard — one step
 * per screen (required plugins → import demo content → explore), with the
 * step chips doubling as navigation. Mirrors the site's own card design —
 * see assets/css/admin.css, which reuses assets/css/variables.css's tokens.
 *
 * @return void
 */
function rentiva_render_setup_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$steps         = rentiva_get_setup_steps();
	$plugins_ready = rentiva_required_plugins_ready();
	$current       = rentiva_get_current_setup_step();
	$done          = array(
		'plugins' => $plugins_ready,
		'demo'    => (bool) get_option( 'rentiva_demo_imported_at' ),
		'finish'  => $plugins_ready && rentiva_homepage_uses_custom_builder(),
	);
	?>
	<div class="wrap rentiva-admin">
		<?php rentiva_render_admin_topbar( 'setup' ); ?>

		<h1><?php esc_html_e( 'Get Rentiva ready', 'rentiva' ); ?></h1>
		<p class="rentiva-admin-intro"><?php esc_html_e( 'Install the required plugins, import the demo content, then jump into your live site — homepage and rentals wired up.', 'rentiva' ); ?></p>

		<nav class="rentiva-steps" aria-label="<?php esc_attr_e( 'Setup steps', 'rentiva' ); ?>">
			<?php foreach ( array_keys( $steps ) as $index => $key ) : ?>
				<?php
				$classes = 'rentiva-step-chip' . ( $done[ $key ] ? ' is-done' : '' );
				$mark    = $done[ $key ] ? '&#10003;' : (string) ( $index + 1 );

				if ( $key === $current ) {
					$open  = '<span class="' . esc_attr( $classes . ' is-current' ) . '" aria-current="step">';
					$close = '</span>';
				} elseif ( $plugins_ready || 0 === $index ) {
					$open  = '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( rentiva_get_setup_step_url( $key ) ) . '">';
					$close = '</a>';
				} else {
					$open  = '<span class="' . esc_attr( $classes . ' is-locked' ) . '" title="' . esc_attr__( 'Complete Step 1 (Required plugins) first', 'rentiva' ) . '">';
					$close = '</span>';
				}
				?>
				<?php echo $open; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attributes escaped above. ?>
					<span class="rentiva-step-chip__check"><?php echo $mark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- an HTML entity or an integer. ?></span>
					<?php echo esc_html( $steps[ $key ] ); ?>
				<?php echo $close; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static closing tag. ?>
			<?php endforeach; ?>
		</nav>

		<div class="rentiva-card">
			<?php
			if ( 'demo' === $current ) {
				rentiva_render_setup_step_demo();
			} elseif ( 'finish' === $current ) {
				rentiva_render_setup_step_finish();
			} else {
				rentiva_render_setup_step_plugins();
			}

			rentiva_render_setup_wizard_nav( $current, $done );
			?>
		</div>
	</div>
	<?php
}

/**
 * Setup wizard, Step 1: required plugin status with Install Now / Activate
 * buttons (driven by assets/js/admin-setup.js).
 *
 * @return void
 */
function rentiva_render_setup_step_plugins() {
	$plugins       = rentiva_get_required_plugins();
	$plugins_ready = rentiva_required_plugins_ready();
	?>
	<div class="rentiva-card__header">
		<div>
			<span class="rentiva-card__eyebrow"><?php echo esc_html( rentiva_get_setup_step_position_label( 'plugins' ) ); ?></span>
			<h2><?php esc_html_e( 'Required plugins', 'rentiva' ); ?></h2>
			<p><?php esc_html_e( 'Elementor powers the homepage builder and the free Booking and Rental Manager for WooCommerce powers rental items, pricing and booking — both install from WordPress.org. Booking and Rental Manager Pro is optional: upload the zip you received with your purchase. WooCommerce is optional too — only needed if you use its cart & checkout instead of the plugin\'s own native checkout.', 'rentiva' ); ?></p>
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
		<div class="rentiva-plugin-row" data-plugin-row data-slug="<?php echo esc_attr( $plugin['slug'] ); ?>" data-required="<?php echo $plugin['required'] ? '1' : '0'; ?>" data-active="<?php echo $plugin['is_active'] ? '1' : '0'; ?>">
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
	<?php
}

/**
 * Setup wizard, Step 2: one-click demo content import.
 *
 * @return void
 */
function rentiva_render_setup_step_demo() {
	$imported_at   = get_option( 'rentiva_demo_imported_at' );
	$problems      = rentiva_demo_import_ready() ? rentiva_get_demo_import_problems() : array();
	$has_demo_item = (bool) array_filter( wp_list_pluck( rentiva_demo_items(), 'title' ), 'rentiva_find_demo_item' );
	// Only flagged once some demo content exists — on a fresh site everything
	// is legitimately "missing" and the plain Not imported state says enough.
	$incomplete    = $problems && ( $imported_at || $has_demo_item );
	$demo_imported = $imported_at && ! $problems;

	if ( $demo_imported ) {
		$badge_class = 'rentiva-badge--ready';
		$badge_text  = __( 'Imported', 'rentiva' );
	} elseif ( $incomplete ) {
		$badge_class = 'rentiva-badge--attention';
		$badge_text  = __( 'Incomplete', 'rentiva' );
	} else {
		$badge_class = 'rentiva-badge--attention';
		$badge_text  = __( 'Not imported', 'rentiva' );
	}
	?>
	<div class="rentiva-card__header">
		<div>
			<span class="rentiva-card__eyebrow"><?php echo esc_html( rentiva_get_setup_step_position_label( 'demo' ) ); ?></span>
			<h2><?php esc_html_e( 'Import demo content', 'rentiva' ); ?></h2>
			<p><?php esc_html_e( 'Rental categories, pickup locations, and sample rental items, so the homepage and archive pages aren\'t empty while you build out your real catalog.', 'rentiva' ); ?></p>
		</div>
		<span class="rentiva-badge <?php echo esc_attr( $badge_class ); ?>" data-rentiva-demo-badge>
			<?php echo esc_html( $badge_text ); ?>
		</span>
	</div>

	<?php if ( $incomplete ) : ?>
		<div class="rentiva-notice rentiva-notice--attention" data-rentiva-demo-problems>
			<strong><?php esc_html_e( 'Some demo content didn\'t import correctly', 'rentiva' ); ?></strong>
			<ul class="rentiva-notice__list">
				<?php foreach ( array_slice( $problems, 0, 8 ) as $problem ) : ?>
					<li><?php echo esc_html( $problem ); ?></li>
				<?php endforeach; ?>
				<?php if ( count( $problems ) > 8 ) : ?>
					<?php /* translators: %d: number of further problems not listed. */ ?>
					<li><?php echo esc_html( sprintf( _n( '…and %d more.', '…and %d more.', count( $problems ) - 8, 'rentiva' ), count( $problems ) - 8 ) ); ?></li>
				<?php endif; ?>
			</ul>
			<p><?php esc_html_e( 'Click Import Demo Content to repair it — anything you\'ve already changed is left as it is.', 'rentiva' ); ?></p>
		</div>
	<?php endif; ?>

	<?php // Always rendered (hidden until imported) so the in-place import can reveal it. ?>
	<div class="rentiva-notice rentiva-notice--success" data-rentiva-demo-notice<?php echo $demo_imported ? '' : ' hidden'; ?>>
		<strong><?php esc_html_e( 'Demo already imported', 'rentiva' ); ?></strong>
		<p data-rentiva-demo-notice-text>
			<?php
			if ( $demo_imported ) {
				printf(
					/* translators: %s: date/time of last import */
					esc_html__( 'Imported on %s. Running again will not duplicate existing demo items.', 'rentiva' ),
					esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $imported_at ) )
				);
			}
			?>
		</p>
	</div>

	<?php if ( rentiva_has_booking_plugin() ) : ?>
		<?php
		// assets/js/admin-setup.js runs these steps in place over AJAX
		// (rentiva_ajax_import_demo_step()); without JS the form posts to
		// admin-post.php and imports everything in one request instead.
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-rentiva-demo-import data-steps="<?php echo esc_attr( wp_json_encode( rentiva_get_demo_import_steps() ) ); ?>">
			<input type="hidden" name="action" value="rentiva_import_demo">
			<?php wp_nonce_field( 'rentiva_import_demo' ); ?>
			<button type="submit" class="button button-primary button-hero rentiva-import-demo-btn">
				<span class="dashicons dashicons-database-import" aria-hidden="true" data-rentiva-import-icon></span>
				<span data-rentiva-import-label><?php esc_html_e( 'Import Demo Content', 'rentiva' ); ?></span>
			</button>
		</form>

		<div class="rentiva-import-progress" data-rentiva-import-progress hidden>
			<div class="rentiva-plugin-progress__bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="<?php esc_attr_e( 'Demo import progress', 'rentiva' ); ?>"><span></span></div>
			<p class="rentiva-plugin-progress__text" aria-live="polite"></p>

			<ul class="rentiva-import-checklist">
				<?php foreach ( rentiva_get_demo_import_groups() as $group => $group_label ) : ?>
					<li class="rentiva-import-checklist__item" data-group="<?php echo esc_attr( $group ); ?>" data-state="pending">
						<span class="rentiva-import-checklist__icon" aria-hidden="true"></span>
						<span class="rentiva-import-checklist__label"><?php echo esc_html( $group_label ); ?></span>
						<span class="rentiva-import-checklist__count"></span>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="rentiva-notice rentiva-notice--attention rentiva-import-progress__error" data-rentiva-import-error hidden>
				<strong><?php esc_html_e( 'Import stopped', 'rentiva' ); ?></strong>
				<p data-rentiva-import-error-text></p>
				<p><button type="button" class="button" data-rentiva-import-retry><?php esc_html_e( 'Retry', 'rentiva' ); ?></button></p>
			</div>
		</div>
	<?php else : ?>
		<p><em><?php esc_html_e( 'Install and activate Booking and Rental Manager for WooCommerce to import demo content.', 'rentiva' ); ?></em></p>
	<?php endif; ?>
	<?php
}

/**
 * Setup wizard, Step 3: shortcuts to the homepage, Theme Settings, and the
 * live site. Only reachable once the required plugins are active (see
 * rentiva_get_current_setup_step()).
 *
 * @return void
 */
function rentiva_render_setup_step_finish() {
	$homepage_ready = rentiva_homepage_uses_custom_builder();
	$front_id       = (int) get_option( 'page_on_front' );

	// The normal page-edit screen rather than a direct deep link into the
	// Elementor editor: for a page already in builder mode, Elementor
	// replaces that screen's editor with its own "Edit with Elementor"
	// call-to-action, which is where the complete page design opens.
	$homepage_edit_url = $homepage_ready
		? get_edit_post_link( $front_id, 'raw' )
		: wp_nonce_url( admin_url( 'admin-post.php?action=rentiva_setup_homepage' ), 'rentiva_setup_homepage' );

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
	<div class="rentiva-card__header">
		<div>
			<span class="rentiva-card__eyebrow"><?php echo esc_html( rentiva_get_setup_step_position_label( 'finish' ) ); ?></span>
			<h2><?php esc_html_e( 'Next steps', 'rentiva' ); ?></h2>
			<p><?php esc_html_e( 'Polish your brand, edit the homepage, and open the site your visitors will see.', 'rentiva' ); ?></p>
		</div>
	</div>

	<div class="rentiva-next-steps">
		<?php foreach ( $next_steps as $step ) : ?>
			<a class="rentiva-next-step-tile" href="<?php echo esc_url( $step['url'] ); ?>" <?php echo $step['blank'] ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
				<span class="dashicons <?php echo esc_attr( $step['icon'] ); ?>" aria-hidden="true"></span>
				<strong><?php echo esc_html( $step['label'] ); ?></strong>
				<span><?php echo esc_html( $step['desc'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * The wizard's Back / Continue footer for the current step.
 *
 * Step 1's Continue ships without an href while any required plugin is
 * inactive; assets/js/admin-setup.js restores it from data-href as soon as
 * the last one activates, so the admin can move on without a reload. Step 2
 * is optional — its forward button reads "Skip this step" until the demo is
 * imported.
 *
 * @param string             $current A key from rentiva_get_setup_steps().
 * @param array<string,bool> $done    Step key => whether that step is complete.
 * @return void
 */
function rentiva_render_setup_wizard_nav( $current, $done ) {
	$step_keys = array_keys( rentiva_get_setup_steps() );
	$index     = (int) array_search( $current, $step_keys, true );
	$prev      = $index > 0 ? $step_keys[ $index - 1 ] : '';
	$next      = isset( $step_keys[ $index + 1 ] ) ? $step_keys[ $index + 1 ] : '';
	$locked    = 'plugins' === $current && ! $done['plugins'];

	if ( ! $next ) {
		$forward_label = __( 'Go to Dashboard', 'rentiva' );
		$forward_url   = admin_url( 'index.php' );
		$forward_class = 'button button-primary';
	} elseif ( 'demo' === $current && ! $done['demo'] ) {
		$forward_label = __( 'Skip this step', 'rentiva' );
		$forward_url   = rentiva_get_setup_step_url( $next );
		$forward_class = 'button';
	} else {
		$forward_label = __( 'Continue', 'rentiva' );
		$forward_url   = rentiva_get_setup_step_url( $next );
		$forward_class = 'button button-primary';
	}
	?>
	<div class="rentiva-wizard-nav">
		<?php if ( $prev ) : ?>
			<a class="button" href="<?php echo esc_url( rentiva_get_setup_step_url( $prev ) ); ?>">
				&larr; <?php esc_html_e( 'Back', 'rentiva' ); ?>
			</a>
		<?php endif; ?>

		<div class="rentiva-wizard-nav__forward">
			<?php if ( $locked ) : ?>
				<span class="rentiva-wizard-nav__hint" data-rentiva-wizard-hint><?php esc_html_e( 'Activate the required plugins to continue.', 'rentiva' ); ?></span>
				<a class="<?php echo esc_attr( $forward_class ); ?> disabled" role="link" aria-disabled="true" data-rentiva-wizard-next data-href="<?php echo esc_url( $forward_url ); ?>">
					<?php echo esc_html( $forward_label ); ?> &rarr;
				</a>
			<?php else : ?>
				<a class="<?php echo esc_attr( $forward_class ); ?>" href="<?php echo esc_url( $forward_url ); ?>" data-rentiva-wizard-forward>
					<?php echo esc_html( $forward_label ); ?> &rarr;
				</a>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
