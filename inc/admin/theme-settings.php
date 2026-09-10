<?php
/**
 * Rentiva → Theme Settings — one wp_options row (`rentiva_settings`) for
 * every homepage-copy override, color override, and integration setting.
 * Read anywhere in the theme via rentiva_get_setting( $key, $default ).
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the top-level "Rentiva" admin menu with two submenus: Setup
 * (inc/admin/setup-wizard.php) and Theme Settings (this file). Both share
 * the parent slug 'rentiva-settings' — the Setup submenu deliberately keeps
 * that exact slug too, so it lands on the parent menu's own URL instead of
 * WordPress auto-generating a redundant duplicate first item.
 *
 * @return void
 */
function rentiva_add_settings_page() {
	add_menu_page(
		__( 'Rentiva', 'rentiva' ),
		__( 'Rentiva', 'rentiva' ),
		'edit_theme_options',
		'rentiva-settings',
		'rentiva_render_setup_page',
		'dashicons-admin-customizer',
		61
	);

	$setup_hook = add_submenu_page(
		'rentiva-settings',
		__( 'Rentiva Setup', 'rentiva' ),
		__( 'Setup', 'rentiva' ),
		'edit_theme_options',
		'rentiva-settings',
		'rentiva_render_setup_page'
	);

	$settings_hook = add_submenu_page(
		'rentiva-settings',
		__( 'Rentiva Theme Settings', 'rentiva' ),
		__( 'Theme Settings', 'rentiva' ),
		'edit_theme_options',
		'rentiva-theme-settings',
		'rentiva_render_settings_page'
	);

	rentiva_admin_page_hooks( array_filter( array( $setup_hook, $settings_hook ) ) );
}
add_action( 'admin_menu', 'rentiva_add_settings_page' );

/**
 * Stores (or, called with no argument, retrieves) the hook suffixes of
 * Rentiva's two admin pages, so rentiva_settings_page_assets() can target
 * both without guessing WordPress's hook-name generation rules.
 *
 * @param string[]|null $hooks Hook suffixes to store, or null to read them back.
 * @return string[]
 */
function rentiva_admin_page_hooks( $hooks = null ) {
	static $stored = array();
	if ( null !== $hooks ) {
		$stored = $hooks;
	}
	return $stored;
}

/**
 * Register the single `rentiva_settings` option + its sanitize callback.
 *
 * @return void
 */
function rentiva_register_settings() {
	register_setting(
		'rentiva_settings_group',
		'rentiva_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'rentiva_sanitize_settings',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'rentiva_register_settings' );

/**
 * Sanitize every field of the settings form on save. Fields not present in
 * the submitted form (e.g. because a section was hidden) are preserved from
 * the existing option rather than dropped.
 *
 * @param array $input Raw $_POST-derived value.
 * @return array
 */
function rentiva_sanitize_settings( $input ) {
	$existing = get_option( 'rentiva_settings', array() );
	if ( ! is_array( $existing ) ) {
		$existing = array();
	}

	// Theme Settings is locked in the UI until Setup's Step 1 (required
	// plugins) is complete — refuse a direct options.php POST too, rather
	// than trusting the UI lock alone.
	if ( ! rentiva_required_plugins_ready() ) {
		add_settings_error( 'rentiva_settings', 'rentiva_locked', __( 'Theme Settings is locked until the required plugins on Rentiva → Setup are active.', 'rentiva' ) );
		return $existing;
	}

	$output = $existing;

	$text_fields = array(
		'hero_eyebrow',
		'hero_title',
		'hero_subtitle',
		'promo_badge',
		'promo_title',
		'promo_text',
		'testimonial_name',
		'testimonial_role',
		'footer_tagline',
		'pickup_hours',
	);
	foreach ( $text_fields as $field ) {
		if ( isset( $input[ $field ] ) ) {
			$output[ $field ] = sanitize_text_field( $input[ $field ] );
		}
	}

	if ( isset( $input['testimonial_quote'] ) ) {
		$output['testimonial_quote'] = sanitize_textarea_field( $input['testimonial_quote'] );
	}

	$id_fields = array( 'hero_image_id', 'promo_image_id', 'why_image_id', 'testimonial_avatar_id' );
	foreach ( $id_fields as $field ) {
		if ( isset( $input[ $field ] ) ) {
			$output[ $field ] = absint( $input[ $field ] );
		}
	}

	$url_fields = array( 'list_item_url' );
	foreach ( $url_fields as $field ) {
		if ( isset( $input[ $field ] ) ) {
			$output[ $field ] = esc_url_raw( $input[ $field ] );
		}
	}

	$color_fields = array( 'color_primary', 'color_primary_dark', 'color_background' );
	foreach ( $color_fields as $field ) {
		if ( isset( $input[ $field ] ) ) {
			$sanitized = sanitize_hex_color( $input[ $field ] );
			if ( $sanitized ) {
				$output[ $field ] = $sanitized;
			}
		}
	}

	if ( isset( $input['single_item_layout'] ) ) {
		$output['single_item_layout'] = in_array( $input['single_item_layout'], array( 'theme', 'plugin' ), true ) ? $input['single_item_layout'] : 'plugin';
	}

	if ( isset( $input['trust_stats'] ) && is_array( $input['trust_stats'] ) ) {
		$stats = array();
		foreach ( $input['trust_stats'] as $stat ) {
			$stats[] = array(
				'value' => isset( $stat['value'] ) ? sanitize_text_field( $stat['value'] ) : '',
				'label' => isset( $stat['label'] ) ? sanitize_text_field( $stat['label'] ) : '',
			);
		}
		$output['trust_stats'] = $stats;
	}

	if ( isset( $input['social_links'] ) && is_array( $input['social_links'] ) ) {
		$social = array();
		foreach ( array( 'twitter', 'instagram', 'facebook' ) as $platform ) {
			$social[ $platform ] = isset( $input['social_links'][ $platform ] ) ? esc_url_raw( $input['social_links'][ $platform ] ) : '';
		}
		$output['social_links'] = $social;
	}

	return $output;
}

/**
 * Enqueue the WP media uploader only on this settings screen.
 *
 * @param string $hook
 * @return void
 */
function rentiva_settings_page_assets( $hook ) {
	if ( ! in_array( $hook, rentiva_admin_page_hooks(), true ) ) {
		return;
	}
	wp_enqueue_media();
	// Core's own AJAX plugin installer/activator (Plugins/Add Plugins screens)
	// — queues one request per plugin (wp.updates.queue) instead of one long
	// blocking call, so admin-setup.js can drive Step 1's Install/Activate
	// buttons through it without risking a PHP execution-time timeout.
	//
	// 'rentiva-admin' and 'rentiva-admin-setup' are both already registered
	// (with the 'updates' dependency and filemtime()-based versioning) by
	// rentiva_register_assets() in inc/enqueue.php, which — being hooked to
	// 'init' — always runs before this admin_enqueue_scripts callback; pass
	// no src/deps/version here, since WP_Dependencies::add() silently
	// ignores them on a handle that's already registered.
	wp_enqueue_script( 'updates' );
	wp_enqueue_style( 'rentiva-admin' );
	wp_enqueue_script( 'rentiva-admin-setup' );
	wp_localize_script(
		'rentiva-admin-setup',
		'rentivaAdmin',
		array(
			'selectImageTitle'   => __( 'Select an image', 'rentiva' ),
			'uploadPluginUrl'    => admin_url( 'plugin-install.php?tab=upload' ),
			'pluginsScreenUrl'   => admin_url( 'plugins.php' ),
			'installingText'     => __( 'Installing…', 'rentiva' ),
			'activatingText'     => __( 'Activating…', 'rentiva' ),
			'activeText'         => __( 'Active', 'rentiva' ),
			'installedText'      => __( 'Installed', 'rentiva' ),
			'installButtonText'  => __( 'Install Now', 'rentiva' ),
			'activateButtonText' => __( 'Activate', 'rentiva' ),
			'retryText'          => __( 'Retry', 'rentiva' ),
			/* translators: %s: plugin name. */
			'phaseInstalling'    => __( 'Installing %s…', 'rentiva' ),
			/* translators: %s: plugin name. */
			'phaseInstalled'     => __( '%s installed! Click Activate to finish.', 'rentiva' ),
			/* translators: %s: plugin name. */
			'phaseActivating'    => __( 'Activating %s…', 'rentiva' ),
			/* translators: %s: plugin name. */
			'phaseDone'          => __( '%s is active!', 'rentiva' ),
			'stallHeading'       => __( 'Still working…', 'rentiva' ),
			'stallHint'          => __( 'This is taking longer than usual — a slow connection or a plugin with a heavier setup routine can do this. It hasn\'t failed; you can keep waiting, or check its status on the Plugins page.', 'rentiva' ),
			'pluginsScreenLinkText' => __( 'Open Plugins page', 'rentiva' ),
			'manualInstallHeading'  => __( 'Automatic installation failed', 'rentiva' ),
			'manualActivateHeading' => __( 'Automatic activation failed', 'rentiva' ),
			'manualWporgHint'       => __( 'You can download it from WordPress.org and upload the zip instead, or install it manually via FTP/File Manager.', 'rentiva' ),
			'manualPremiumHint'     => __( 'This plugin isn\'t available in the WordPress.org repository. Upload the zip file you received, or extract it into wp-content/plugins/ via FTP/File Manager, then click Activate.', 'rentiva' ),
			'manualActivateHint'    => __( 'The plugin is installed but WordPress couldn\'t activate it automatically — see the error above for the reason. You can try activating it directly from the Plugins page instead.', 'rentiva' ),
			'uploadLinkText'        => __( 'Upload plugin zip', 'rentiva' ),
			'wporgLinkText'         => __( 'View on WordPress.org', 'rentiva' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'rentiva_settings_page_assets' );

/**
 * The settings screen markup.
 *
 * @return void
 */
function rentiva_render_settings_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	if ( ! rentiva_required_plugins_ready() ) {
		?>
		<div class="wrap rentiva-admin">
			<?php rentiva_render_admin_topbar( 'settings' ); ?>

			<h1><?php esc_html_e( 'Theme Settings', 'rentiva' ); ?></h1>

			<div class="rentiva-card">
				<div class="rentiva-notice rentiva-notice--attention">
					<strong><?php esc_html_e( 'Locked until Step 1 is complete', 'rentiva' ); ?></strong>
					<p><?php esc_html_e( 'Install and activate the required plugins on Rentiva → Setup before configuring Theme Settings.', 'rentiva' ); ?></p>
				</div>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=rentiva-settings' ) ); ?>">
					<?php esc_html_e( 'Go to Setup', 'rentiva' ); ?>
				</a>
			</div>
		</div>
		<?php
		return;
	}

	$settings = get_option( 'rentiva_settings', array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	$social = isset( $settings['social_links'] ) && is_array( $settings['social_links'] ) ? $settings['social_links'] : array();

	$tabs = array(
		'footer'       => __( 'Footer & Social', 'rentiva' ),
		'colors'       => __( 'Colors', 'rentiva' ),
		'integrations' => __( 'Integrations', 'rentiva' ),
	);
	?>
	<div class="wrap rentiva-admin">
		<?php rentiva_render_admin_topbar( 'settings' ); ?>

		<h1><?php esc_html_e( 'Theme Settings', 'rentiva' ); ?></h1>
		<p class="rentiva-admin-intro">
			<?php esc_html_e( 'Sitewide colors, footer, and behavior — homepage copy, photos, and layout are edited directly on the Homepage page in Elementor.', 'rentiva' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=rentiva-settings' ) ); ?>"><?php esc_html_e( 'Open Rentiva Setup', 'rentiva' ); ?></a>
		</p>

		<form method="post" action="options.php" class="rentiva-card rentiva-settings-layout">
			<?php settings_fields( 'rentiva_settings_group' ); ?>

			<nav class="rentiva-settings-nav">
				<?php foreach ( $tabs as $slug => $label ) : ?>
					<button type="button" class="rentiva-settings-nav__item" data-tab="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></button>
				<?php endforeach; ?>
			</nav>

			<div class="rentiva-settings-content">

				<section class="rentiva-settings-tab" data-tab="footer">
					<h2><?php esc_html_e( 'Footer & Social', 'rentiva' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="rentiva_footer_tagline"><?php esc_html_e( 'Tagline', 'rentiva' ); ?></label></th>
							<td><input type="text" id="rentiva_footer_tagline" class="regular-text" name="rentiva_settings[footer_tagline]" value="<?php echo esc_attr( $settings['footer_tagline'] ?? '' ); ?>"></td>
						</tr>
						<?php foreach ( array( 'twitter' => 'Twitter / X', 'instagram' => 'Instagram', 'facebook' => 'Facebook' ) as $platform => $label ) : ?>
							<tr>
								<th scope="row"><label for="rentiva_social_<?php echo esc_attr( $platform ); ?>"><?php echo esc_html( $label ); ?></label></th>
								<td><input type="url" id="rentiva_social_<?php echo esc_attr( $platform ); ?>" class="regular-text" name="rentiva_settings[social_links][<?php echo esc_attr( $platform ); ?>]" value="<?php echo esc_attr( $social[ $platform ] ?? '' ); ?>" placeholder="https://"></td>
							</tr>
						<?php endforeach; ?>
					</table>
				</section>

				<section class="rentiva-settings-tab" data-tab="colors">
					<h2><?php esc_html_e( 'Colors', 'rentiva' ); ?></h2>
					<table class="form-table" role="presentation">
						<?php
						$colors = array(
							'color_primary'      => array( __( 'Primary', 'rentiva' ), '#1B5E3B' ),
							'color_primary_dark' => array( __( 'Primary (hover)', 'rentiva' ), '#154D2E' ),
							'color_background'   => array( __( 'Background', 'rentiva' ), '#F9F8F5' ),
						);
						foreach ( $colors as $key => $meta ) :
							list( $label, $default ) = $meta;
							?>
							<tr>
								<th scope="row"><label for="rentiva_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
								<td><input type="text" id="rentiva_<?php echo esc_attr( $key ); ?>" class="rentiva-color-field" name="rentiva_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $settings[ $key ] ?? $default ); ?>"></td>
							</tr>
						<?php endforeach; ?>
					</table>
				</section>

				<section class="rentiva-settings-tab" data-tab="integrations">
					<h2><?php esc_html_e( 'Integrations', 'rentiva' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Single item layout', 'rentiva' ); ?></th>
							<td>
								<?php $layout = $settings['single_item_layout'] ?? 'plugin'; ?>
								<label><input type="radio" name="rentiva_settings[single_item_layout]" value="theme" <?php checked( $layout, 'theme' ); ?>> <?php esc_html_e( 'Rentiva design', 'rentiva' ); ?></label><br>
								<label><input type="radio" name="rentiva_settings[single_item_layout]" value="plugin" <?php checked( $layout, 'plugin' ); ?>> <?php esc_html_e( "Plugin's own bundled design (recommended)", 'rentiva' ); ?></label>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="rentiva_list_item_url"><?php esc_html_e( '"List Your Item" link', 'rentiva' ); ?></label></th>
							<td><input type="url" id="rentiva_list_item_url" class="regular-text" name="rentiva_settings[list_item_url]" value="<?php echo esc_attr( $settings['list_item_url'] ?? '' ); ?>" placeholder="https://"></td>
						</tr>
						<tr>
							<th scope="row"><label for="rentiva_pickup_hours"><?php esc_html_e( 'Default pickup hours', 'rentiva' ); ?></label></th>
							<td><input type="text" id="rentiva_pickup_hours" class="regular-text" name="rentiva_settings[pickup_hours]" value="<?php echo esc_attr( $settings['pickup_hours'] ?? '' ); ?>" placeholder="Available daily 8:00 AM – 8:00 PM"></td>
						</tr>
					</table>
				</section>

				<div class="rentiva-settings-save">
					<?php submit_button( __( 'Save Settings', 'rentiva' ), 'primary', 'submit', false ); ?>
					<span class="rentiva-settings-save__hint"><?php esc_html_e( 'Changes apply sitewide after save.', 'rentiva' ); ?></span>
				</div>
			</div>
		</form>
	</div>
	<?php
}
