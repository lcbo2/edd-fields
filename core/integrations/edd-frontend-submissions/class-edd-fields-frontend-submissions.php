<?php
/**
 * EDD Frontend Submissions Integration
 *
 * @since 1.0.0
 *
 * @package EDD_Fields
 * @subpackage EDD_Fields/core/integrations/edd-frontend-submissions
 */

defined( 'ABSPATH' ) || die();

class EDD_Fields_Frontend_Submissions {

	/**
	 * EDD_Fields_Frontend_Submissions constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		
		add_filter( 'fes_load_fields_array', array( $this, 'add_formbuilder_fields' ) );

	}
	
	/**
	 * Adds custom formbuilder fields.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $fields
	 */
	public function add_formbuilder_fields( $fields ) {

		require_once __DIR__ . '/class-edd-fields-formbuilder-field.php';

		$fields['edd_fields'] = 'EDD_Fields_FormBuilderField';

		return $fields;
		
	}

}

$integrate = new EDD_Fields_Frontend_Submissions();