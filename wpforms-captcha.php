<?php
/**
 * Plugin Name:       WPForms Custom Captcha
 * Plugin URI:        https://wpforms.com
 * Description:       Captcha fields with WPForms.
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Author:            WPForms
 * Author URI:        https://wpforms.com
 * Version:           1.7.0
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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WPForms.Comments.PHPDocDefine.MissPHPDoc
// Plugin version.
define( 'WPFORMS_CAPTCHA_VERSION', '1.7.0' );
// phpcs:enable WPForms.Comments.PHPDocDefine.MissPHPDoc

/**
 * Load the provider class.
 *
 * @since 1.0.0
 */
function wpforms_captcha() {
	// Check requirements.
	if ( ! wpforms_captcha_required() ) {
		return;
	}

	require_once plugin_dir_path( __FILE__ ) . 'class-captcha.php';
}

add_action( 'wpforms_loaded', 'wpforms_captcha' );

/**
 * Check requirements.
 *
 * @since 1.6.0
 */
function wpforms_captcha_required() {

	if ( PHP_VERSION_ID < 50600 ) {
		add_action( 'admin_notices', 'wpforms_captcha_fail_php_version' );

		return false;
	}

	if ( ! function_exists( 'wpforms' ) ) {
		return false;
	}

	if ( version_compare( wpforms()->version, '1.7.5', '<' ) ) {
		add_action( 'admin_notices', 'wpforms_captcha_fail_wpforms_version' );
		add_action( 'admin_init', 'wpforms_captcha_deactivation' );

		return false;
	}

	if (
		! function_exists( 'wpforms_get_license_type' ) ||
		! in_array( wpforms_get_license_type(), [ 'basic', 'plus', 'pro', 'elite', 'agency', 'ultimate' ], true )
	) {
		return false;
	}

	return true;
}

/**
 * Admin notice for a minimum PHP version.
 *
 * @since 1.6.0
 */
function wpforms_captcha_fail_php_version() {

	echo '<div class="notice notice-error"><p>';
	printf(
		wp_kses( /* translators: %s - WPForms.com documentation page URI. */
			__( 'The WPForms Custom Captcha plugin has been deactivated. Your site is running an outdated version of PHP that is no longer supported and is not compatible with the plugin. <a href="%s" target="_blank" rel="noopener noreferrer">Read more</a> for additional information.', 'wpforms-captcha' ),
			[
				'a' => [
					'href'   => [],
					'rel'    => [],
					'target' => [],
				],
			]
		),
		esc_url( wpforms_utm_link( 'https://wpforms.com/docs/supported-php-version/', 'all-plugins', 'Custom Captcha PHP Notice' ) )
	);
	echo '</p></div>';
}

/**
 * Admin notice for minimum WPForms version.
 *
 * @since 1.6.0
 */
function wpforms_captcha_fail_wpforms_version() {

	echo '<div class="notice notice-error"><p>';
	esc_html_e( 'The WPForms Custom Captcha plugin has been deactivated, because it requires WPForms v1.7.5 or later to work.', 'wpforms-captcha' );
	echo '</p></div>';
}

/**
 * Deactivate the plugin.
 *
 * @since 1.6.0
 */
function wpforms_captcha_deactivation() {

	deactivate_plugins( plugin_basename( __FILE__ ) );

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
}

/**
 * Load the plugin updater.
 *
 * @since 1.0.0
 *
 * @param string $key WPForms license key.
 */
function wpforms_captcha_updater( $key ) {

	new WPForms_Updater(
		[
			'plugin_name' => 'WPForms Captcha',
			'plugin_slug' => 'wpforms-captcha',
			'plugin_path' => plugin_basename( __FILE__ ),
			'plugin_url'  => trailingslashit( plugin_dir_url( __FILE__ ) ),
			'remote_url'  => WPFORMS_UPDATER_API,
			'version'     => WPFORMS_CAPTCHA_VERSION,
			'key'         => $key,
		]
	);
}
add_action( 'wpforms_updater', 'wpforms_captcha_updater' );
