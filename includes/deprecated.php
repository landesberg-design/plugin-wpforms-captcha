<?php
/**
 * Deprecated functions.
 * This file is used to keep backward compatibility with older versions of the plugin.
 * The functions and classes listed below will be removed in December 2023.
 *
 * @since 1.8.0
 */

/**
 * Check requirements.
 *
 * @since 1.6.0
 * @deprecated 1.8.0
 */
function wpforms_captcha_required() {

	_deprecated_function( __FUNCTION__, '1.8.0 of the WPForms Custom Captcha addon' );
}


/**
 * Admin notice for a minimum PHP version.
 *
 * @since 1.6.0
 * @deprecated 1.8.0
 */
function wpforms_captcha_fail_php_version() {

	_deprecated_function( __FUNCTION__, '1.8.0 of the WPForms Custom Captcha addon' );
}

/**
 * Admin notice for minimum WPForms version.
 *
 * @since 1.6.0
 * @deprecated 1.8.0
 */
function wpforms_captcha_fail_wpforms_version() {

	_deprecated_function( __FUNCTION__, '1.8.0 of the WPForms Custom Captcha addon' );
}

/**
 * Deactivate the plugin.
 *
 * @since 1.6.0
 * @deprecated 1.8.0
 */
function wpforms_captcha_deactivation() {

	_deprecated_function( __FUNCTION__, '1.8.0 of the WPForms Custom Captcha addon' );
}

/**
 * Load the plugin updater.
 *
 * @since 1.0.0
 * @deprecated 1.8.0
 *
 * @param string $key WPForms license key.
 */
function wpforms_captcha_updater( $key ) {

	_deprecated_function( __FUNCTION__, '1.8.0 of the WPForms Custom Captcha addon' );
}

/***
 * Legacy `WPForms_Captcha_Field` class was moved to the new `WPFormsCaptcha\Plugin` class.
 *
 * @since 1.8.0
 */
class_alias( 'WPFormsCaptcha\Plugin', 'WPForms_Captcha_Field' );
