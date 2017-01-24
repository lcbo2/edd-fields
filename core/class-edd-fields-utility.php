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
		
		$ebook = apply_filters( 'edd_fields_ebook_template_defaults', array(
			'label' => _x( 'Ebook', 'Ebook Template', EDD_Fields_ID ),
			'icon' => 'dashicons dashicons-book',
			'fields' => array(
				array(
					'label' => _x( 'Author', 'Ebook Template: Author', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Number of Pages', 'Ebook Template: Number of Pages', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Publisher', 'Ebook Template: Publisher', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Format', 'Ebook Template: Format', EDD_Fields_ID ),
				),
			),
		) );
		
		$wp_plugin = apply_filters( 'edd_fields_wordpress_plugin_template_defaults', array(
			'label' => _x( 'WordPress Plugin', 'WordPress Plugin Template', EDD_Fields_ID ),
			'icon' => 'dashicons dashicons-admin-plugins',
			'fields' => array(
				array(
					'label' => _x( 'Required WordPress Version', 'WordPress Plugin Template: Required WordPress Version', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Required PHP Version', 'WordPress Plugin Template: Required PHP Version', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Contributors', 'WordPress Plugin Template: Contributors', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Supported Languages', 'WordPress Plugin Template: Supported Languages', EDD_Fields_ID ),
				),
			),
		) );
		
		$wp_theme = apply_filters( 'edd_fields_wordpress_theme_template_defaults', array(
			'label' => _x( 'WordPress Theme', 'WordPress Theme Template', EDD_Fields_ID ),
			'icon' => 'dashicons dashicons-admin-appearance',
			'fields' => array(
				array(
					'label' => _x( 'Required WordPress Version', 'WordPress Theme Template: Required WordPress Version', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Required PHP Version', 'WordPress Theme Template: Required PHP Version', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Contributors', 'WordPress Theme Template: Contributors', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Supported Languages', 'WordPress Theme Template: Supported Languages', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Responsive', 'WordPress Theme Template: Responsive', EDD_Fields_ID ),
				),
			),
		) );

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
				array(
					'label' => _x( 'Length', 'Music Template: Length', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Producer', 'Music Template: Producer', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Explicit', 'Music Template: Explicit', EDD_Fields_ID ),
				),
			),
		) );

		$software = apply_filters( 'edd_fields_software_template_defaults', array(
			'label' => _x( 'Software', 'Software Template', EDD_Fields_ID ),
			'icon' => 'dashicons dashicons-editor-code',
			'fields' => array(
				array(
					'label' => _x( 'Operating System', 'Software Template: Operating System', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'File Type', 'Software Template: File Type', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Disk Space', 'Software Template: Disk Space', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Required Memory', 'Software Template: Required Memory', EDD_Fields_ID ),
				),
			),
		) );
		
		$photography = apply_filters( 'edd_fields_photography_template_defaults', array(
			'label' => _x( 'Photography', 'Photography Template', EDD_Fields_ID ),
			'icon' => 'dashicons dashicons-format-image',
			'fields' => array(
				array(
					'label' => _x( 'License', 'Photography Template: License', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'File Type', 'Photography Template: File Type', EDD_Fields_ID ),
				),
				array(
					'label' => _x( 'Dimensions', 'Photography Template: Dimensions', EDD_Fields_ID ),
				),
			),
		) );

		return apply_filters( 'edd_fields_template_defaults', array_merge( 
			array( $ebook ),
			array( $wp_plugin ),
			array( $wp_theme ),
			array( $music ),
			array( $software ),
			array( $photography )
		) );

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
		
		// Ensure we've got an Integer, because PHP is silly and thinks '0' is False
		$index = (int) $index;
		
		$templates = $this->get_templates();
		
		if ( ! isset( $templates[ $index ] ) ) return 'Custom';
		
		return $templates[ $index ]['label'];
		
	}
	
	/**
	 * Grabs the Template Index based on a provided Name. If one is not found, false is returned
	 * 
	 * @param		string $name Template Name
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		integer|boolean Template Index
	 */
	public function get_template_index_by_name( $name ) {
		
		// Normally it is already provided like this, but this ensures that it is the case
		$name = EDDFIELDS()->utility->sanitize_key( $name );
		
		$templates = $this->get_templates();
		
		$template_index = false; // Default case
		for ( $index = 0; $index < count( $templates ); $index++ ) {
			
			$template = $templates[ $index ];
			$template_name = EDDFIELDS()->utility->sanitize_key( $template['label'] );
			
			// If the provided name matches the Template Name, update $template_index
			if ( $name == $template_name ) $template_index = $index;
			
		}
		
		// If there's no matching template, return 1 higher than the number of Templates
		if ( $template_index === false ) $template_index = count( $templates );
		
		return $template_index;
		
	}
	
	/**
	 * Sanitize a String to have only Alphanumeric characters. No special characters, spaces, etc.
	 * 
	 * @param		string $key Template/Field Key
	 *                                    
	 * @access		public
	 * @since		1.0.0
	 * @return		string Sanitized Key
	 */
	public function sanitize_key( $key ) {
		
		// Matches non-words, including underscore.
		$key = preg_replace( '[\W|_]', '', strtolower( $key ) );
		
		return apply_filters( 'edd_fields_key_sanitize', $key );
		
	}
	
	/**
	 * Whether or not the [edd_fields_table] shortcode should be injected in a Post
	 * 
	 * @param		integer $post_id Post ID
	 *                               
	 * @access		public
	 * @since		1.0.0
	 * @return		boolean True to inject, false to not inject
	 */
	public function is_shortcode_injected( $post_id = null ) {
		
		if ( $post_id === null ) $post_id = get_the_ID();
		
		$inject_shortcode = get_post_meta( $post_id, 'edd_fields_table_inject', true );
		
		if ( $inject_shortcode == 'checked' ) {
			$inject_shortcode = true;
		}
		else if ( $inject_shortcode == 'unchecked' ) {
			$inject_shortcode = false;
		}
		else {
			// Default unchecked, but we want the inverse of this option
			$inject_shortcode = ! edd_get_option( 'edd_fields_table_inject', false );
		}
		
		return $inject_shortcode;
		
	}
	
}