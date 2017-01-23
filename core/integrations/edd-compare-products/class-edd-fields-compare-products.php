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
		
		add_filter( 'edd_compare_products_meta_after', array( $this, 'add_field_keys' ) );

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

}

$integrate = new EDD_Fields_Compare_Products();