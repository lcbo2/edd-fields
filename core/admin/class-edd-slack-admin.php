<?php
/**
 * The admin settings side to EDD Slack
 *
 * @since 1.0.0
 *
 * @package EDD_Slack
 * @subpackage EDD_Slack/core/admin
 */

defined( 'ABSPATH' ) || die();

class EDD_Slack_Admin {

    /**
	 * edd_slack_Admin constructor.
	 *
	 * @since 1.0.0
	 */
    function __construct() {

        // Register Settings Section
        add_filter( 'edd_settings_sections_extensions', array( $this, 'settings_section' ) );

        // Register Settings
        add_filter( 'edd_settings_extensions', array( $this, 'settings' ) );

        // Enqueue CSS/JS on our Admin Settings Tab
        add_action( 'edd_settings_tab_top_extensions_edd-slack-settings', array( $this, 'admin_settings_scripts' ) );

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

        $sections['edd-slack-settings'] = __( 'Slack', EDD_Slack::$plugin_id );

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

        $edd_slack_settings = array(
            array(
                'id'   => 'edd_slack_template_settings',
                'name' => __( 'Field Template Groups', EDD_Slack::$plugin_id ),
                'type' => 'repeater',
                'classes' => array( 'edd-slack-settings-repeater' ),
                'add_item_text' => __( 'Add Field Template Group', EDD_Slack::$plugin_id ),
                'delete_item_text' => __( 'Remove Field Template Group', EDD_Slack::$plugin_id ),
                'collapsable' => true,
                'collapsable_title' => __( 'New Field Template Group', EDD_Slack::$plugin_id ),
                'fields' => array(
                    'field_template_group_name' => array(
                        'type'  => 'text',
                        'desc' => __( 'Field Template Group Name', EDD_Slack::$plugin_id ),
                    ),
                    'test'    => array(
                        'type'  => 'text',
                        'desc' => __( 'Another Field', EDD_Slack::$plugin_id ),
                    ),
                    'fields' => array(
                        'test' => true,
                        'type' => 'repeater',
                        'desc' => __( 'Fields', EDD_Slack::$plugin_id ),
                        'add_item_text' => __( 'Add Field', EDD_Slack::$plugin_id ),
                        'delete_item_text' => __( 'Remove Field', EDD_Slack::$plugin_id ),
                        'collapsable' => false,
                        'fields' => array(
                            'field_name' => array( 
                                'type'  => 'text',
                                'desc' => __( 'Field Name', EDD_Slack::$plugin_id ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        // If EDD is at version 2.5 or later...
        if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
            // Place the Settings in our Settings Section
            $edd_slack_settings = array( 'edd-slack-settings' => $edd_slack_settings );
        }

        return array_merge( $settings, $edd_slack_settings );

    }

    /**
     * Enqueue our CSS/JS on our Admin Settings Tab
     * 
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function admin_settings_scripts() {

        wp_enqueue_style( EDD_Slack::$plugin_id . '-admin' );
        wp_enqueue_script( EDD_Slack::$plugin_id . '-admin' );

    }

}