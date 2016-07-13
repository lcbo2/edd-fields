<?php
/*
Plugin Name: Easy Digital Downloads - Fields
Plugin URL: http://easydigitaldownloads.com/downloads/fields
Description: Easily create custom attributes or meta for your Downloads
Version: 1.0.0
Text Domain: edd-fields
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
         * @var         Plugin ID used for Localization, script names, etc.
         * @since       1.0.0
         */
        public static $plugin_id = 'edd-fields';

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
            
            //$plugin_data = get_plugin_data( __FILE__, false );

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

            // Add Our Fields Metabox
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
            
            // Save our Metabox Data
            add_action( 'save_post', array( $this, 'save_post' ) );
            
            // Register our CSS/JS
            add_action( 'init', array( $this, 'register_scripts' ) );
            
            // Enqueue CSS/JS on the Admin Side
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            
            // Output on Frontend
            add_shortcode( 'edd_fields', array( $this, 'output' ) );
            
            // Force our Shortcode on Download Singles
            // Priority of 9 puts it above the purchase button
            add_filter( 'the_content', array( $this, 'inject_shortcode' ), 9 );

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
            $locale = apply_filters( 'plugin_locale', get_locale(), EDD_Fields::$plugin_id );
            $mofile = sprintf( '%1$s-%2$s.mo', EDD_Fields::$plugin_id, $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/' . EDD_Fields::$plugin_id . '/' . $mofile;

            if ( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-fields/ folder
                // This way translations can be overridden via the Theme/Child Theme
                load_textdomain( EDD_Fields::$plugin_id, $mofile_global );
            }
            else if ( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-fields/languages/ folder
                load_textdomain( EDD_Fields::$plugin_id, $mofile_local );
            }
            else {
                // Load the default language files
                load_plugin_textdomain( EDD_Fields::$plugin_id, false, $lang_dir );
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
            );

            // If EDD is at version 2.5 or later...
            if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
                // Place the Settings in our Settings Section
                $edd_fields_settings = array( 'edd-fields-settings' => $edd_fields_settings );
            }

            return array_merge( $settings, $edd_fields_settings );

        }

        /**
         * Add our mutatable Meta Box for EDD Fields
         * 
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function add_meta_boxes() {

            $post_types = apply_filters( 'edd_download_metabox_post_types' , array( 'download' ) );

            foreach ( $post_types as $post_type ) {

                add_meta_box(
                    'edd_fields_meta_box', // Metabox ID
                    sprintf( __( '%1$s Fields', 'easy-digital-downloads' ), edd_get_label_singular(), edd_get_label_plural() ), // Metabox Label
                    array( $this, 'fields' ), // Callback function to populate Meta Box
                    $post_type,
                    'normal', // Position
                    'high' // Priority
                );

            }

        }

        /**
         * Our mutable Meta Box content
         * 
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function fields() {
            
            $fields = get_post_meta( get_the_ID(), 'edd_fields', true );

            ob_start(); ?>

            <table id="edd-fields-repeater" class="wp-list-table widefat fixed posts">

                <thead>
                    <tr>
                        <th scope="col" class="edd-fields-field-handle"></th>
                        <th scope="col" class="edd-fields"><?php _e( 'Label', EDD_Fields::$plugin_id ); ?></th>
                        <th scope="col" class="edd-fields-field-label"><?php _e( 'Value', EDD_Fields::$plugin_id ); ?></th>
                        <th scope="col"><?php _e( 'Remove', EDD_Fields::$plugin_id ); ?></th>
                    </tr>
                </thead>
            
            <?php if ( count( $fields ) > 0 ) : 
            
                for ( $index = 0; $index < count( $fields ); $index++ ) : ?>

                        <tr>
                            <td><span class="handle dashicons dashicons-sort"></span></td>
                            <td class="edd-fields-key">
                                <?php echo EDD()->html->text( array(
                                    'name' => "edd_fields[$index][key]",
                                    'value' => $fields[$index]['key']
                                ) ); ?>
                            </td>
                            <td class="edd-fields-value">
                                <?php echo EDD()->html->text( array(
                                    'name' => "edd_fields[$index][value]",
                                    'value' => $fields[$index]['value']
                                ) ); ?>
                            </td>
                            <td>
                                <span class="edd-remove-row button-secondary"><?php _e( 'Remove Field', EDD_Fields::$plugin_id ); ?></span>
                            </td>
                        </tr>

                    <?php 

                endfor;
            
            else : ?>

                <tr>
                    <td><span class="handle dashicons dashicons-sort"></span></td>
                    <td class="edd-fields-key">
                        <?php echo EDD()->html->text( array(
                            'name' => 'butts[0][key]'
                        ) ); ?>
                    </td>
                    <td class="edd-fields-value">
                        <?php echo EDD()->html->text( array(
                            'name' => 'edd_fields[0][value]'
                        ) ); ?>
                    </td>
                    <td>
                        <span class="edd-remove-row button-secondary"><?php _e( 'Remove Field', EDD_Fields::$plugin_id ); ?></span>
                    </td>
                </tr>
            
            <?php endif; ?>

            </table>

            <p>
                <span id="edd-fields-add-row" class="button-secondary" ><?php _e( 'Add Field', EDD_Fields::$plugin_id ); ?></span>
            </p>
            
            <?php echo ob_get_clean();
            
        }
        
        public function save_post( $post_id ) {
            
            $post_types = apply_filters( 'edd_download_metabox_post_types' , array( 'download' ) );
            
            if ( in_array( get_post_type(), $post_types ) ) {

                $new_fields = ! empty( $_POST['edd_fields'] ) ? array_values( $_POST['edd_fields'] ) : array();
                
                update_post_meta( $post_id, 'edd_fields', $new_fields );
                
            }
            
        }
        
        public function register_scripts() {
            
            wp_register_style(
                EDD_Fields::$plugin_id . '-admin',
                EDD_Fields_URL . '/admin.css',
                null,
                defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : EDD_Fields_VER
            );
            
            wp_register_script(
                EDD_Fields::$plugin_id . '-admin',
                EDD_Fields_URL . '/admin.js',
                array( 'jquery' ),
                defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : EDD_Fields_VER,
                true
            );
            
        }
        
        /**
         * Enqueue our CSS/JS on the Admin Side
         * 
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function admin_enqueue_scripts() {
            
            $current_screen = get_current_screen();
            global $pagenow;
            
            $post_types = apply_filters( 'edd_download_metabox_post_types' , array( 'download' ) );
            
            if ( ( in_array( $current_screen->post_type, $post_types ) ) && ( in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) ) ) {
            
                wp_enqueue_style( EDD_Fields::$plugin_id . '-admin' );
                wp_enqueue_script( EDD_Fields::$plugin_id . '-admin' );
                
            }
            
        }
        
        /**
         * Outputs Download Fields as a table via Shortcode
         * 
         * @access      public
         * @since       1.0.0
         * @return      HTML
         */
        public function output( $atts, $content ) {
            
            $atts = shortcode_atts( 
                array(
                    'class' => '',
                    'post_id' => get_the_ID(),
                ), 
                $atts,
                'edd_fields'
            );
            
            ob_start();
            
            $repeater = get_post_meta( $atts['post_id'], 'edd_fields', true );
            
            if ( count( $repeater ) > 0 ) : ?>

                <table class="edd-fields<?php echo ( $atts['class'] !== '' ) ? ' ' . $atts['class'] : ''; ?>">

                <?php foreach ( $repeater as $row ) : ?>
            
                    <tr>
                    
                        <td>
                            <?php echo $row['key']; ?>
                        </td>

                        <td>
                            <?php echo $row['value']; ?>
                        </td>
                        
                    </tr>

                <?php endforeach; ?>
                    
                </table>
            
            <?php endif;
            
            $output = ob_get_contents();
            ob_get_clean();
            
            return $output;
            
        }
        
        /**
         * Force our Shortcode to load on Single Downloads
         * @param  string $content The Content
         * @return string The Content
         */
        public function inject_shortcode( $content ) {
            
            if ( is_single() && get_post_type() == 'download' ) {
                $content .= '[edd_fields]';
            }
            
            return $content;
            
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