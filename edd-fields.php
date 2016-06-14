<?php
/*
Plugin Name: Easy Digital Downloads - Fields
Plugin URL: http://easydigitaldownloads.com/downloads/fields
Description: Easily create custom attributes or meta for your Downloads
Version: 1.0.0
Text Domain: efields
Author: Real Big Plugins
Author URI: http://realbigplugins.com
Contributors: d4mation
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'EDD_Fields' ) ) {

    /**
     * Main EDD_Fields class
     *
     * @since       1.0.0
     */
    class EDD_Fields {

        /**
         * @var         EDD_Fields $instance The one true EDD_Fields
         * @since       1.0.0
         */
        private static $instance;

        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true EDD_Fields
         */
        public static function instance() {

            if( ! self::$instance ) {

                self::$instance = new EDD_Fields();
                self::$instance->setup_constants();
                self::$instance->load_textdomain();
                self::$instance->hooks();

            }

            return self::$instance;

        }

        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {

            // Plugin version
            define( 'EDD_Fields_VER', '1.0.0' );

            // Plugin path
            define( 'EDD_Fields_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_Fields_URL', plugin_dir_url( __FILE__ ) );

        }

        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {

            // Register Settings Section
            add_filter( 'edd_settings_sections_extensions', array( $this, 'settings_section' ) );

            // Register Settings
            add_filter( 'edd_settings_extensions', array( $this, 'settings' ) );

            // Handle licensing
            if ( class_exists( 'EDD_License' ) ) {
                $license = new EDD_License( __FILE__, 'Easy Digital Downloads - Fields', EDD_Fields_VER, 'Real Big Plugins' );
            }

        }

        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {

            // Set filter for language directory
            $lang_dir = EDD_Fields_DIR . '/languages/';
            $lang_dir = apply_filters( 'EDD_Fields_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'efields' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'efields', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/efields/' . $mofile;

            if ( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/efields/ folder
                // This way translations can be overridden via the Theme/Child Theme
                load_textdomain( 'efields', $mofile_global );
            }
            else if ( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/efields/languages/ folder
                load_textdomain( 'efields', $mofile_local );
            }
            else {
                // Load the default language files
                load_plugin_textdomain( 'efields', false, $lang_dir );
            }

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

            $sections['efields-settings'] = __( 'Fields', 'efields' );

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

            $efields_settings = array(
            );

            // If EDD is at version 2.5 or later...
            if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
                // Place the Settings in our Settings Section
                $efields_settings = array( 'efields-settings' => $efields_settings );
            }

            return array_merge( $settings, $efields_settings );

        }

    }

} // End Class Exists Check

/**
 * The main function responsible for returning the one true EDD_Fields
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDD_Fields The one true EDD_Fields
 */
function EDD_Fields_load() {

    if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {

        if ( ! class_exists( 'EDD_Extension_Activation' ) ) {
            require_once 'includes/class.extension-activation.php';
        }

        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();

    }
    else {
        return EDD_Fields::instance();
    }

}
add_action( 'plugins_loaded', 'EDD_Fields_load' );