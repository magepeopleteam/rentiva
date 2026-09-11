<?php
/**
 * Admin-only bootstrap. Loaded last from functions.php, and only ever
 * touches wp-admin screens — nothing here runs on the front end.
 *
 * @package Rentiva
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_admin() ) {
	return;
}

require_once RENTIVA_DIR . 'inc/admin/theme-settings.php';
require_once RENTIVA_DIR . 'inc/admin/setup-wizard.php';
