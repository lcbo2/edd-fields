<?php
/**
 * Installer class
 *
 * @since 1.0.0
 *
 * @package EDD_Fields
 * @subpackage EDD_Fields/core
 */

defined( 'ABSPATH' ) || die();

class EDD_Fields_Install {

	/**
	 * Installs the plugin. Fires on plugin activation.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	static function install() {

		self::set_default_settings();
	}

	/**
	 * Saves default settings to the database.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private static function set_default_settings() {

		// Field Template Groups
		$saved_templates = edd_get_option( 'edd_fields_template_settings' );

		// -1 means they've been erased manually. False means they've never been set
		if ( $saved_templates === false ) {

			$default_templates = get_edd_fields_default_templates();
			edd_update_option( 'edd_fields_template_settings', $default_templates );
			
		}
	}
}