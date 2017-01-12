<?php
/**
 * The admin settings side to EDD Fields
 *
 * @since 1.0.0
 *
 * @package EDD_Fields
 * @subpackage EDD_Fields/core/admin
 */

defined( 'ABSPATH' ) || die();

class EDD_Fields_Admin {

	/**
	 * EDD_Fields_Admin constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {

		// Register Settings Section
		add_filter( 'edd_settings_sections_extensions', array( $this, 'settings_section' ) );

		// Register Settings
		add_filter( 'edd_settings_extensions', array( $this, 'settings' ) );

		// Enqueue CSS/JS on our Admin Settings Tab
		add_action( 'edd_settings_tab_top_extensions_edd-fields-settings', array( $this, 'admin_settings_scripts' ) );

	}
	
	/**
	* Register Our Settings Section
	* 
	* @access	   public
	* @since		1.0.0
	* @param		array $sections EDD Settings Sections
	* @return	   array Modified EDD Settings Sections
	*/
	public function settings_section( $sections ) {

		$sections['edd-fields-settings'] = __( 'Fields', EDD_Fields_ID );

		return $sections;

	}

	/**
	* Adds new Settings Section under "Extensions". Throws it under Misc if EDD is lower than v2.5
	* 
	* @access	  public
	* @since	   1.0.0
	* @param	   array $settings The existing EDD settings array
	* @return	  array The modified EDD settings array
	*/
	public function settings( $settings ) {

		$edd_fields_settings = array(
			array(
				'id'   => 'edd_fields_template_settings',
				'name' => __( 'Field Template Groups', EDD_Fields_ID ),
				'type' => 'fields_repeater',
				'classes' => array( 'edd-fields-settings-repeater' ),
				'add_item_text' => __( 'Add Field Template Group', EDD_Fields_ID ),
				'delete_item_text' => __( 'Remove Field Template Group', EDD_Fields_ID ),
				'collapsable' => true,
				'collapsable_title' => __( 'New Field Template Group', EDD_Fields_ID ),
				'std' => $this->get_default_templates(),
				'fields' => $this->get_template_fields(),
			),
		);

		// If EDD is at version 2.5 or later...
		if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
			// Place the Settings in our Settings Section
			$edd_fields_settings = array( 'edd-fields-settings' => $edd_fields_settings );
		}

		return array_merge( $settings, $edd_fields_settings );

	}
	
	public function get_default_templates() {
		
		$music = apply_filters( 'edd_fields_music_template_defaults', array(
			'label' => _x( 'Music', 'Music Template', EDD_Fields_ID ),
			'icon' => 'dashicons-format-audio',
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
			'icon' => 'dashicons-editor-code',
			'fields' => array(
				'file_type' => array(
					'type' => 'text',
					'desc' => _x( 'File Type', 'Software Template: File Type', EDD_Fields_ID ),
				),
			),
		) );
		
		return array_merge( array( $music ), array( $software ) );
		
	}
	
	public function get_template_fields() {
		
		$fields = apply_filters( 'edd_fields_template_fields', array(
			'label' => array(
				'type' => 'text',
				'desc' => _x( 'Template Name', 'Template Name Label', EDD_Fields_ID ),
				'field_class' => '',
				'readonly' => false,
			),
			'icon' => array(
				'type' => 'text',
				'desc' => _x( 'Icon', 'Template Tabl Icon Label', EDD_Fields_ID ),
				'field_class' => '',
				'readonly' => false,
			),
			'fields' => array(
				'type' => 'fields_repeater',
				'fields' => array(
					'label' => array(
						'type' => 'text',
						'desc' => _x( 'Field Name', 'Field Name Label', EDD_Fields_ID ),
				'field_class' => '',
				'readonly' => false,
					),
				),
			),
		) );
		
		return $fields;
		
	}

	/**
	 * Enqueue our CSS/JS on our Admin Settings Tab
	 * 
	 * @access	  public
	 * @since	   1.0.0
	 * @return	  void
	 */
	public function admin_settings_scripts() {

		wp_enqueue_style( EDD_Fields_ID . '-admin' );
		wp_enqueue_script( EDD_Fields_ID . '-admin' );

	}

}