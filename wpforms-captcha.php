<?php
/**
 * Plugin Name:       WPForms Custom Captcha
 * Plugin URI:        https://wpforms.com
 * Description:       Captcha fields with WPForms.
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Author:            WPForms
 * Author URI:        https://wpforms.com
 * Version:           1.8.0
 * Text Domain:       wpforms-captcha
 * Domain Path:       languages
 *
 * WPForms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * WPForms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WPForms. If not, see <https://www.gnu.org/licenses/>.
 */

use WPFormsCaptcha\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin version.
 *
 * @since 1.0.0
 */
const WPFORMS_CAPTCHA_VERSION = '1.8.0';

/**
 * Plugin file.
 *
 * @since 1.8.0
 */
const WPFORMS_CAPTCHA_FILE = __FILE__;

/**
 * Plugin path.
 *
 * @since 1.8.0
 */
define( 'WPFORMS_CAPTCHA_PATH', plugin_dir_path( WPFORMS_CAPTCHA_FILE ) );

/**
 * Plugin URL.
 *
 * @since 1.8.0
 */
define( 'WPFORMS_CAPTCHA_URL', plugin_dir_url( WPFORMS_CAPTCHA_FILE ) );

/**
 * Check addon requirements.
 *
 * @since 1.0.0
 * @since 1.8.0 Uses requirements feature.
 */
function wpforms_captcha_load() {

	$requirements = [
		'file'    => WPFORMS_CAPTCHA_FILE,
		'wpforms' => '1.8.3',
	];

	if ( ! function_exists( 'wpforms_requirements' ) || ! wpforms_requirements( $requirements ) ) {
		return;
	}

	wpforms_captcha();
}

add_action( 'wpforms_loaded', 'wpforms_captcha_load' );

/**
 * Get the instance of the addon main class.
 *
 * @since 1.8.0
 *
 * @return Plugin
 */
function wpforms_captcha() {

	require_once WPFORMS_CAPTCHA_PATH . 'vendor/autoload.php';

	return Plugin::get_instance();
}
