<?php
/**
 * Installer class
 *
 * @since {{VERSION}}
 *
 * @package EDD_Fields
 * @subpackage EDD_Fields/core
 */

defined( 'ABSPATH' ) || die();

class EDD_Fields_Install {

	/**
	 * Installs the plugin. Fires on plugin activation.
	 *
	 * @since {{VERSION}}
	 * @access private
	 */
	static function install() {

		self::set_default_settings();
	}

	/**
	 * Saves default settings to the database.
	 *
	 * @since {{VERSION}}
	 * @access private
	 */
	private static function set_default_settings() {

		// Field Template Groups
		$saved_templates = edd_get_option( 'edd_fields_template_settings' );

		// -1 means they've been erased manually. False means they've never been set
		if ( $saved_templates === false ) {

			$default_templates = EDDFIELDS()->utility->get_default_templates();
			edd_update_option( 'edd_fields_template_settings', $default_templates );
		}
	}
}