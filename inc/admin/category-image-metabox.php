<?php
/**
 * Adds an image field to `rbfw_item_caregory` terms (stored as term meta
 * `rentiva_category_image_id`), used by template-parts/home/categories.php
 * and template-parts/cards/category-card.php.
 *
 * The plugin's own taxonomy registration (admin/taxonomy_register.php) has
 * no image field of its own, so this is a genuine theme-owned enrichment —
 * not a duplicate of existing plugin functionality.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wires up the term-meta hooks once the booking plugin's `rbfw_item`
 * post type actually exists.
 *
 * This can't be an unconditional top-level `if ( ! rentiva_has_booking_plugin() ) return;`
 * guard: this file is `require_once`'d from functions.php, which runs
 * before the `init` hook — the point where the plugin registers
 * `rbfw_item` (admin/custom_post.php). Checking `post_type_exists()`
 * that early always fails, so the add_action() calls below it would
 * silently never run even on a site with the plugin active.
 *
 * @return void
 */
function rentiva_register_category_image_field_hooks() {
	if ( ! rentiva_has_booking_plugin() ) {
		return;
	}

	add_action( 'rbfw_item_caregory_add_form_fields', 'rentiva_category_image_add_field' );
	add_action( 'rbfw_item_caregory_edit_form_fields', 'rentiva_category_image_edit_field' );
	add_action( 'created_rbfw_item_caregory', 'rentiva_save_category_image_field' );
	add_action( 'edited_rbfw_item_caregory', 'rentiva_save_category_image_field' );
	add_action( 'admin_enqueue_scripts', 'rentiva_category_image_assets' );
}
add_action( 'init', 'rentiva_register_category_image_field_hooks', 20 );

/**
 * "Add new term" form field.
 *
 * @return void
 */
function rentiva_category_image_add_field() {
	wp_nonce_field( 'rentiva_category_image', 'rentiva_category_image_nonce' );
	?>
	<div class="form-field">
		<label for="rentiva_category_image_id"><?php esc_html_e( 'Category image', 'rentiva' ); ?></label>
		<div class="rentiva-image-field" data-field="rentiva_category_image_id">
			<div class="rentiva-image-field__preview"></div>
			<input type="hidden" id="rentiva_category_image_id" name="rentiva_category_image_id" class="rentiva-image-field__input" value="">
			<button type="button" class="button rentiva-image-field__select"><?php esc_html_e( 'Select image', 'rentiva' ); ?></button>
			<button type="button" class="button rentiva-image-field__remove" style="display:none"><?php esc_html_e( 'Remove', 'rentiva' ); ?></button>
		</div>
		<p><?php esc_html_e( 'Shown on the homepage "Explore What You Need" category tile.', 'rentiva' ); ?></p>
	</div>
	<?php
}

/**
 * "Edit term" form field.
 *
 * @param WP_Term $term
 * @return void
 */
function rentiva_category_image_edit_field( $term ) {
	wp_nonce_field( 'rentiva_category_image', 'rentiva_category_image_nonce' );
	$image_id = (int) get_term_meta( $term->term_id, 'rentiva_category_image_id', true );
	?>
	<tr class="form-field">
		<th scope="row"><label for="rentiva_category_image_id"><?php esc_html_e( 'Category image', 'rentiva' ); ?></label></th>
		<td>
			<div class="rentiva-image-field" data-field="rentiva_category_image_id">
				<div class="rentiva-image-field__preview">
					<?php if ( $image_id ) : ?>
						<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
					<?php endif; ?>
				</div>
				<input type="hidden" id="rentiva_category_image_id" name="rentiva_category_image_id" class="rentiva-image-field__input" value="<?php echo esc_attr( $image_id ); ?>">
				<button type="button" class="button rentiva-image-field__select"><?php esc_html_e( 'Select image', 'rentiva' ); ?></button>
				<button type="button" class="button rentiva-image-field__remove" <?php echo $image_id ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Remove', 'rentiva' ); ?></button>
			</div>
			<p class="description"><?php esc_html_e( 'Shown on the homepage "Explore What You Need" category tile.', 'rentiva' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Persist the field on both add and edit.
 *
 * @param int $term_id
 * @return void
 */
function rentiva_save_category_image_field( $term_id ) {
	if ( ! isset( $_POST['rentiva_category_image_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rentiva_category_image_nonce'] ) ), 'rentiva_category_image' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}

	if ( isset( $_POST['rentiva_category_image_id'] ) ) {
		$image_id = absint( $_POST['rentiva_category_image_id'] );
		if ( $image_id ) {
			update_term_meta( $term_id, 'rentiva_category_image_id', $image_id );
		} else {
			delete_term_meta( $term_id, 'rentiva_category_image_id' );
		}
	}
}

/**
 * Load the media uploader script on the taxonomy's add/edit screens.
 *
 * @param string $hook
 * @return void
 */
function rentiva_category_image_assets( $hook ) {
	if ( ! in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) ) {
		return;
	}
	if ( ! isset( $_GET['taxonomy'] ) || 'rbfw_item_caregory' !== $_GET['taxonomy'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check, not a state change.
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
