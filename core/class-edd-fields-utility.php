<?php
/**
 * Utility functions that don't make sense to have available as global PHP Functions
 *
 * @since 1.0.0
 *
 * @package EDD_Fields
 * @subpackage EDD_Fields/core
 */

defined( 'ABSPATH' ) || die();

class EDD_Fields_Utility {

	/**
	 * Returns the Default Templates if none are saved. This overrides any default values for the Fields
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		array Default Templates
	 */
	public function get_default_templates() {

		$music = apply_filters( 'edd_fields_music_template_defaults', array(
			'label' => _x( 'Music', 'Music Template', EDD_Fields_ID ),
			'icon' => 'dashicons dashicons-format-audio',
			'fields' => array(
				array(
					'label' => _x( 'Artist', 'Music Template: Artist', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Genre', 'Music Template: Genre', EDD_Fields_ID ),
				),
			),
		) );

		$software = apply_filters( 'edd_fields_software_template_defaults', array(
			'label' => _x( 'Software', 'Software Template', EDD_Fields_ID ),
			'icon' => 'dashicons dashicons-editor-code',
			'fields' => array(
				array(
					'label' => _x( 'File Type', 'Software Template: File Type', EDD_Fields_ID ),
				),
			),
		) );

		return array_merge( array( $music ), array( $software ) );

	}
	
	/**
	 * Grabs either the saved Templates or Defaults as appropriate
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		array Field Group Templates
	 */
	public function get_templates() {
		
		$templates = edd_get_option( 'edd_fields_template_settings', false );
		
		if ( ! $templates ) $templates = $this->get_default_templates();
		
		return $templates;
		
	}
	
	/**
	 * Grabs the Template Name based on a provided Index. If one is not found, "Custom" is returned.
	 * 
	 * @param		integer $index Zero-Indexed array Index of the Template
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		string  Template Name
	 */
	public function get_template_name_by_index( $index ) {
		
		$templates = $this->get_templates();
		
		if ( ! isset( $templates[ $index ] ) ) return 'Custom';
		
		return $templates[ $index ]['label'];
		
	}
	
}