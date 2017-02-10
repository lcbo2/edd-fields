<?php
/**
 * EDD Compare Products Integration
 *
 * @since 1.0.0
 *
 * @package EDD_Fields
 * @subpackage EDD_Fields/core/integrations/edd-compare-products
 */

defined( 'ABSPATH' ) || die();

class EDD_Fields_Compare_Products {

	/**
	 * EDD_Fields_Compare_Products constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		
		// Add some "fake" keys for EDD Fields Templates
		// We're hooking into "After" because we don't care if they may or may not be set in the most recent Download
		add_filter( 'edd_compare_products_meta_after', array( $this, 'add_field_keys' ) );
		
		// Filter the value for each EDD Fields key to display the proper value
		add_filter( 'edd_compare_products_display_value', array( $this, 'get_edd_fields_value' ), 10, 2 );
		
		// Remove Keys that shouldn't be used with Compare Products
		add_filter( 'edd_compare_products_exclude_meta', array( $this, 'remove_unneeded_keys' ) );

	}
	
	/**
	 * Add EDD Fields Templates to EDD Compare Products' Settings Page
	 * 
	 * @param		array $fields Key/Value pairs of Meta for Downloads
	 *                                                   
	 * @access		public
	 * @since		1.0.0
	 * @return		array Key/Value pairs with our Pre-Defined Templates added
	 */
	public function add_field_keys( $fields ) {
		
		$templates = EDDFIELDS()->utility->get_templates();
		
		foreach ( $templates as $template ) {
			
			$template_name = EDDFIELDS()->utility->sanitize_key( $template['label'] );
			
			foreach ( $template['fields'] as $field ) {
				
				$field_name = EDDFIELDS()->utility->sanitize_key( $field['label'] );
			
				$key = 'eddfields-' . $template_name . '-' . $field_name;

				$fields[ $key ] = $template['label'] . ': ' . $field['label'];
				
			}
			
		}
		
		return $fields;
		
	}
	
	/**
	 * Use the Fake Key to determine with EDD Fields Template and Key to grab
	 * 
	 * @param		string $value Old Value
	 * @param		string $key   Key from Compare Products
	 *                                        
	 * @access		public
	 * @since		1.0.0
	 * @return		string New Value
	 */
	public function get_edd_fields_value( $value, $key ) {
		
		// Doesn't start with "eddfields-", not one of ours
		if ( strpos( $key, 'eddfields-' ) !== 0 ) return $value;
		
		// Used to grab the Post ID
		global $post;

		// Grab the Template Key and the Field Key from the selected Key in Compare Products
		$key = explode( '-', $key );

		$template_key = $key[1];
		$field_key = $key[2];
		
		return edd_fields_get( $field_key, $post->ID, $template_key );
		
	}
	
	/**
	 * Remove Keys that either won't display data in Compare Products or display "useless" data
	 * 
	 * @param		array $keys Keys to be removed
	 *                                
	 * @access		public
	 * @since		1.0.0
	 * @return		array Updated Keys
	 */
	public function remove_unneeded_keys( $keys ) {
		
		$keys[] = 'edd_fields';
		$keys[] = 'edd_fields_template';
		
		return $keys;
		
	}

}

$integrate = new EDD_Fields_Compare_Products();