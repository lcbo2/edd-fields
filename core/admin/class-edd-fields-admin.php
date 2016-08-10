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
    * @access       public
    * @since        1.0.0
    * @param        array $sections EDD Settings Sections
    * @return       array Modified EDD Settings Sections
    */
    public function settings_section( $sections ) {

        $sections['edd-fields-settings'] = __( 'Fields', EDD_Fields::$plugin_id );

        return $sections;

    }

    /**
    * Adds new Settings Section under "Extensions". Throws it under Misc if EDD is lower than v2.5
    * 
    * @access      public
    * @since       1.0.0
    * @param       array $settings The existing EDD settings array
    * @return      array The modified EDD settings array
    */
    public function settings( $settings ) {

        $edd_fields_settings = array(
            array(
                'id'   => 'edd_fields_template_settings',
                'name' => __( 'Field Template Groups', EDD_Fields::$plugin_id ),
                'type' => 'repeater',
                'classes' => array( 'edd-fields-settings-repeater' ),
                'add_item_text' => __( 'Add Field Template Group', EDD_Fields::$plugin_id ),
                'delete_item_text' => __( 'Remove Field Template Group', EDD_Fields::$plugin_id ),
                'collapsable' => true,
                'fields' => array(
                    'field_template_group_name' => array(
                        'type'  => 'text',
                        'label' => __( 'Field Template Group Name', EDD_Fields::$plugin_id ),
                    ),
                    'test'    => array(
                        'type'  => 'text',
                        'label' => __( 'Another Field', EDD_Fields::$plugin_id ),
                    ),
                    'fields' => array(
                        'test' => true,
                        'type' => 'repeater',
                        'label' => __( 'Fields', EDD_Fields::$plugin_id ),
                        'add_item_text' => __( 'Add Field', EDD_Fields::$plugin_id ),
                        'delete_item_text' => __( 'Remove Field', EDD_Fields::$plugin_id ),
                        'collapsable' => true,
                        'fields' => array(
                            'field_name' => array( 
                                'type'  => 'text',
                                'label' => __( 'Field Name', EDD_Fields::$plugin_id ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        // If EDD is at version 2.5 or later...
        if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
            // Place the Settings in our Settings Section
            $edd_fields_settings = array( 'edd-fields-settings' => $edd_fields_settings );
        }

        return array_merge( $settings, $edd_fields_settings );

    }

    /**
     * Enqueue our CSS/JS on our Admin Settings Tab
     * 
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function admin_settings_scripts() {

        wp_enqueue_style( EDD_Fields::$plugin_id . '-admin' );
        wp_enqueue_script( EDD_Fields::$plugin_id . '-admin' );

    }

}