<?php
/**
 * Rentiva theme bootstrap.
 *
 * This file only defines constants and loads the theme's modules from inc/.
 * No presentation logic or rental/pricing/availability logic lives here —
 * see docs/architecture.md for where each concern belongs.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Disallow direct access.
}

/** Theme version — bump on every release; also used as a cache-busting asset suffix. */
define( 'RENTIVA_VERSION', '1.0.0' );

/** Absolute filesystem path to the theme, no trailing slash. */
define( 'RENTIVA_DIR', trailingslashit( get_template_directory() ) );

/** Base URI of the theme, no trailing slash. */
define( 'RENTIVA_URI', trailingslashit( get_template_directory_uri() ) );

/** Minimum WordPress/PHP the theme targets — kept in sync with style.css header. */
define( 'RENTIVA_MIN_PHP', '7.4' );

/**
 * Core modules. Order matters: helpers before anything that calls them,
 * setup before enqueue, integrations after template-functions so they can
 * reuse the shared render layer, admin last since it's admin-only.
 */
$rentiva_includes = array(
	'inc/helpers.php',
	'inc/setup.php',
	'inc/enqueue.php',
	'inc/template-functions.php',
	'inc/template-hooks.php',
	'inc/integrations/booking-plugin.php',
	'inc/integrations/woocommerce.php',
	'inc/integrations/elementor.php',
	'inc/demo-import/sample-data.php',
	'inc/demo-import/importer.php',
	'inc/admin/admin.php', // Loads inc/admin/theme-settings.php + inc/admin/setup-wizard.php + inc/admin/item-specs-metabox.php itself.
);

foreach ( $rentiva_includes as $rentiva_include ) {
	$rentiva_include_path = RENTIVA_DIR . $rentiva_include;
	if ( is_readable( $rentiva_include_path ) ) {
		require_once $rentiva_include_path;
	}
}
unset( $rentiva_includes, $rentiva_include, $rentiva_include_path );
