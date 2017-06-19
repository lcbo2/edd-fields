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
		
		$templates = edd_fields_get_templates();
		
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
		$name = edd_fields_sanitize_key( $name );
		
		$templates = edd_fields_get_templates();
		
		$template_index = false; // Default case
		for ( $index = 0; $index < count( $templates ); $index++ ) {
			
			$template = $templates[ $index ];
			$template_name = edd_fields_sanitize_key( $template['label'] );
			
			// If the provided name matches the Template Name, update $template_index
			if ( $name == $template_name ) $template_index = $index;
			
		}
		
		// If there's no matching template, return 1 higher than the number of Templates
		if ( $template_index === false ) $template_index = count( $templates );
		
		return $template_index;
		
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
	
	/**
	 * Inserts an Array at the specified Index
	 * 
	 * @param		array   $array        Original Array
	 * @param		integer $position     Numeric Index at which to Insert. This does work with Associative Arrays, but you need to provide a numeric index regardless
	 * @param		array   $insert_array Inserted Array
	 *              
	 * @access		public
	 * @since		1.0.0
	 * @return		array   Modified Array
	 */
	public function array_insert( $array, $position, $insert_array ) {
		
		// First half before the cut-off for the splice
		$first_array = array_splice( $array, 0, $position ); 
		
		// Merge this with the inserted array and the last half of the splice
		return array_merge( $first_array, $insert_array, $array ); 
		
	}
	
}