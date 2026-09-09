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
 * Register the settings page as a top-level admin menu item.
 *
 * @return void
 */
function rentiva_add_settings_page() {
	add_menu_page(
		__( 'Rentiva Settings', 'rentiva' ),
		__( 'Rentiva Settings', 'rentiva' ),
		'edit_theme_options',
		'rentiva-settings',
		'rentiva_render_settings_page',
		'dashicons-admin-customizer',
		61
	);
}
add_action( 'admin_menu', 'rentiva_add_settings_page' );

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
		$output['single_item_layout'] = in_array( $input['single_item_layout'], array( 'theme', 'plugin' ), true ) ? $input['single_item_layout'] : 'theme';
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
	if ( 'toplevel_page_rentiva-settings' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'rentiva-admin', RENTIVA_URI . 'assets/css/admin.css', array(), RENTIVA_VERSION );
	wp_enqueue_script( 'rentiva-admin-setup', RENTIVA_URI . 'assets/js/admin-setup.js', array( 'jquery' ), RENTIVA_VERSION, true );
	wp_localize_script(
		'rentiva-admin-setup',
		'rentivaAdmin',
		array(
			'selectImageTitle' => __( 'Select an image', 'rentiva' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'rentiva_settings_page_assets' );

/**
 * Render one image-picker field (hidden ID input + WP media uploader button).
 *
 * @param string $key   Settings key.
 * @param array  $value Current settings array.
 * @return void
 */
function rentiva_render_image_field( $key, $value ) {
	$image_id = isset( $value[ $key ] ) ? (int) $value[ $key ] : 0;
	?>
	<div class="rentiva-image-field" data-field="<?php echo esc_attr( $key ); ?>">
		<div class="rentiva-image-field__preview">
			<?php if ( $image_id ) : ?>
				<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
			<?php endif; ?>
		</div>
		<input type="hidden" name="rentiva_settings[<?php echo esc_attr( $key ); ?>]" class="rentiva-image-field__input" value="<?php echo esc_attr( $image_id ); ?>">
		<button type="button" class="button rentiva-image-field__select"><?php esc_html_e( 'Select image', 'rentiva' ); ?></button>
		<button type="button" class="button rentiva-image-field__remove" <?php echo $image_id ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Remove', 'rentiva' ); ?></button>
	</div>
	<?php
}

/**
 * The settings screen markup.
 *
 * @return void
 */
function rentiva_render_settings_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$settings = get_option( 'rentiva_settings', array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	$stats = isset( $settings['trust_stats'] ) && is_array( $settings['trust_stats'] ) ? $settings['trust_stats'] : array();
	while ( count( $stats ) < 4 ) {
		$stats[] = array( 'value' => '', 'label' => '' );
	}

	$social = isset( $settings['social_links'] ) && is_array( $settings['social_links'] ) ? $settings['social_links'] : array();
	?>
	<div class="wrap rentiva-settings">
		<h1><?php esc_html_e( 'Rentiva Settings', 'rentiva' ); ?></h1>
		<p><?php esc_html_e( 'Override the homepage copy, images, colors and integration behavior. Anything left blank falls back to the built-in design defaults.', 'rentiva' ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'rentiva_settings_group' ); ?>

			<h2 class="title"><?php esc_html_e( 'Hero', 'rentiva' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="rentiva_hero_eyebrow"><?php esc_html_e( 'Eyebrow', 'rentiva' ); ?></label></th>
					<td><input type="text" id="rentiva_hero_eyebrow" class="regular-text" name="rentiva_settings[hero_eyebrow]" value="<?php echo esc_attr( $settings['hero_eyebrow'] ?? '' ); ?>" placeholder="RENT • RIDE • EXPLORE"></td>
				</tr>
				<tr>
					<th scope="row"><label for="rentiva_hero_title"><?php esc_html_e( 'Headline', 'rentiva' ); ?></label></th>
					<td><input type="text" id="rentiva_hero_title" class="regular-text" name="rentiva_settings[hero_title]" value="<?php echo esc_attr( $settings['hero_title'] ?? '' ); ?>" placeholder="Rent. Ride. Explore."></td>
				</tr>
				<tr>
					<th scope="row"><label for="rentiva_hero_subtitle"><?php esc_html_e( 'Subheading', 'rentiva' ); ?></label></th>
					<td><input type="text" id="rentiva_hero_subtitle" class="large-text" name="rentiva_settings[hero_subtitle]" value="<?php echo esc_attr( $settings['hero_subtitle'] ?? '' ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Background photo', 'rentiva' ); ?></th>
					<td><?php rentiva_render_image_field( 'hero_image_id', $settings ); ?></td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Trust Strip', 'rentiva' ); ?></h2>
			<table class="form-table" role="presentation">
				<?php foreach ( $stats as $index => $stat ) : ?>
					<tr>
						<th scope="row">
							<?php
							printf(
								/* translators: %d: stat position, 1-4 */
								esc_html__( 'Stat %d', 'rentiva' ),
								absint( $index + 1 )
							);
							?>
						</th>
						<td>
							<input type="text" class="small-text" name="rentiva_settings[trust_stats][<?php echo esc_attr( $index ); ?>][value]" value="<?php echo esc_attr( $stat['value'] ); ?>" placeholder="10,000+">
							<input type="text" class="regular-text" name="rentiva_settings[trust_stats][<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $stat['label'] ); ?>" placeholder="rentals completed">
						</td>
					</tr>
				<?php endforeach; ?>
			</table>

			<h2 class="title"><?php esc_html_e( 'Promo Banner', 'rentiva' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="rentiva_promo_badge"><?php esc_html_e( 'Badge text', 'rentiva' ); ?></label></th>
					<td><input type="text" id="rentiva_promo_badge" class="regular-text" name="rentiva_settings[promo_badge]" value="<?php echo esc_attr( $settings['promo_badge'] ?? '' ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="rentiva_promo_title"><?php esc_html_e( 'Headline', 'rentiva' ); ?></label></th>
					<td><input type="text" id="rentiva_promo_title" class="regular-text" name="rentiva_settings[promo_title]" value="<?php echo esc_attr( $settings['promo_title'] ?? '' ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="rentiva_promo_text"><?php esc_html_e( 'Text', 'rentiva' ); ?></label></th>
					<td><input type="text" id="rentiva_promo_text" class="large-text" name="rentiva_settings[promo_text]" value="<?php echo esc_attr( $settings['promo_text'] ?? '' ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Background photo', 'rentiva' ); ?></th>
					<td><?php rentiva_render_image_field( 'promo_image_id', $settings ); ?></td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Why Rentiva', 'rentiva' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Photo', 'rentiva' ); ?></th>
					<td><?php rentiva_render_image_field( 'why_image_id', $settings ); ?></td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Testimonial', 'rentiva' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="rentiva_testimonial_quote"><?php esc_html_e( 'Quote', 'rentiva' ); ?></label></th>
					<td><textarea id="rentiva_testimonial_quote" class="large-text" rows="3" name="rentiva_settings[testimonial_quote]"><?php echo esc_textarea( $settings['testimonial_quote'] ?? '' ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="rentiva_testimonial_name"><?php esc_html_e( 'Name', 'rentiva' ); ?></label></th>
					<td><input type="text" id="rentiva_testimonial_name" class="regular-text" name="rentiva_settings[testimonial_name]" value="<?php echo esc_attr( $settings['testimonial_name'] ?? '' ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="rentiva_testimonial_role"><?php esc_html_e( 'Role', 'rentiva' ); ?></label></th>
					<td><input type="text" id="rentiva_testimonial_role" class="regular-text" name="rentiva_settings[testimonial_role]" value="<?php echo esc_attr( $settings['testimonial_role'] ?? '' ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Avatar', 'rentiva' ); ?></th>
					<td><?php rentiva_render_image_field( 'testimonial_avatar_id', $settings ); ?></td>
				</tr>
			</table>

			<h2 class="title"><?php esc_html_e( 'Footer & Social', 'rentiva' ); ?></h2>
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

			<h2 class="title"><?php esc_html_e( 'Colors', 'rentiva' ); ?></h2>
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

			<h2 class="title"><?php esc_html_e( 'Integrations', 'rentiva' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Single item layout', 'rentiva' ); ?></th>
					<td>
						<?php $layout = $settings['single_item_layout'] ?? 'theme'; ?>
						<label><input type="radio" name="rentiva_settings[single_item_layout]" value="theme" <?php checked( $layout, 'theme' ); ?>> <?php esc_html_e( 'Rentiva design (recommended)', 'rentiva' ); ?></label><br>
						<label><input type="radio" name="rentiva_settings[single_item_layout]" value="plugin" <?php checked( $layout, 'plugin' ); ?>> <?php esc_html_e( "Plugin's own bundled design", 'rentiva' ); ?></label>
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

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
