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
            
            // Add Rows to our Repeater
            add_action( 'edd_fields_render_row', array( $this, 'edd_fields_render_row' ), 10, 4 );
            
            // Save our Metabox Data
            add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
            
            // Register our CSS/JS
            add_action( 'init', array( $this, 'register_scripts' ) );
            
            // Enqueue CSS/JS on the Admin Side
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            
            // Output on Frontend
            add_shortcode( 'edd_fields_table', array( $this, 'table_output' ) );
            
            // Grab inividual value via Shortcode
            add_shortcode( 'edd_field', array( $this, 'edd_field_shortcode' ) );
            
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

            $post_types = apply_filters( 'edd_fields_metabox_post_types' , array( 'download' ) );

            foreach ( $post_types as $post_type ) {
                
                $post_type_labels = get_post_type_object( $post_type );
                $post_type_labels = $post_type_labels->labels;

                add_meta_box(
                    'edd_fields_meta_box', // Metabox ID
                    sprintf( __( '%1$s Fields', EDD_Fields::$plugin_id ), $post_type_labels->singular_name, $post_type_labels->name ), // Metabox Label
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
        public function fields( $post ) {
            
            $fields = get_post_meta( get_the_ID(), 'edd_fields', true );
            
            ob_start(); ?>

            <div class="edd_meta_table_wrap">

                <table id="edd-fields-repeater" class="widefat edd_repeatable_table" width="100%" cellpadding="0" cellspacing="0">

                    <thead>
                        <tr>
                            <th scope="col" class="edd-fields-field-handle"></th>
                            <th scope="col" class="edd-fields-name"><?php _e( 'Name', EDD_Fields::$plugin_id ); ?></th>
                            <th scope="col" class="edd-fields-value"><?php _e( 'Value', EDD_Fields::$plugin_id ); ?></th>
                            <th scope="col"></th>
                        </tr>
                    </thead>

                <?php if ( ! empty( $fields ) ) : 

                    foreach ( $fields as $key => $value ) : 
            
                            $name = isset( $value['key'] ) ? $value['key'] : '';
                            $value = isset( $value['value'] ) ? $value['value'] : '';
                            $args = apply_filters( 'edd_fields_row_args', compact( 'name', 'value' ), $post->ID );

                            do_action( 'edd_fields_render_row', $key, $args );

                    endforeach;

                else :

                    do_action( 'edd_fields_render_row', 0, array(), $post->ID, 0 );

                endif; ?>

                    <tr>
                        <td class="submit" colspan="4" style="float: none; clear:both; background:#fff;">
                            <button class="button-secondary edd_add_repeatable" style="margin: 6px 0;"><?php _e( 'Add Field', EDD_Fields::$plugin_id ); ?></button>
                        </td>
                    </tr>

                </table>
                
            </div>

            <?php wp_nonce_field( basename( __FILE__ ), 'edd_fields_meta_box_nonce' ); ?>
            
            <?php echo ob_get_clean();
            
        }
        
        /**
         * Function to render each row of our Repeater in the Metabox
         * 
         * @param       integer $key Array Key
         * @param       array $args Holds HTML Name and Value
         *                          
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function edd_fields_render_row( $key, $args ) {
            
            $defaults = array(
                'name' => '',
                'value' => '',
            );
            
            $args = wp_parse_args( $args, $defaults );
            
            ?>
            
            <tr class="edd_variable_prices_wrapper edd_repeatable_row" data-key="<?php echo esc_attr( $key ); ?>">
                <td>
                    <span class="edd_draghandle"></span>
                    <input type="hidden" name="edd_fields[<?php echo $key; ?>][index]" class="edd_repeatable_index" value="<?php echo $key; ?>"/>
                </td>
                <td class="edd-fields-key">
                    <?php echo EDD()->html->text( array(
                        'name' => "edd_fields[$key][key]",
                        'value' => $args['name'],
                    ) ); ?>
                </td>
                <td class="edd-fields-value">
                    <?php echo EDD()->html->text( array(
                        'name' => "edd_fields[$key][value]",
                        'value' => $args['value'],
                    ) ); ?>
                </td>
                <td>
                    <button class="edd_remove_repeatable" data-type="file" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">
                        <span class="screen-reader-text"><?php _e( 'Remove Field', EDD_Fields::$plugin_id ); ?></span><span aria-hidden="true">&times;</span>
                    </button>
                </td>
            </tr>
            
            <?php
            
        }
        
        /**
         * Save Our Custom Post Meta
         * 
         * @param       integer $post_id Current Post ID
         *                                      
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function save_post( $post_id, $post ) {
            
            if ( ! isset( $_POST['edd_fields_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['edd_fields_meta_box_nonce'], basename( __FILE__ ) ) ) {
                return;
            }
            
            if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
                return;
            }
            
            if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
                return;
            }
            
            if ( ! current_user_can( 'edit_product', $post_id ) ) {
                return;
            }
            
            $post_types = apply_filters( 'edd_fields_metabox_post_types' , array( 'download' ) );
            
            if ( in_array( $post->post_type, $post_types ) ) {

                if ( ! empty( $_POST['edd_fields'] ) ) {
                    
                    // Sanitization Filter. Values are already forced to re-index on Save.
                    $new_fields = apply_filters( 'edd_metabox_save_fields', array_values( $_POST['edd_fields'] ) );

                    if ( ( count( $new_fields ) == 1 ) &&
                        ( empty( $new_fields[0]['key'] ) ) ) {
                        delete_post_meta( $post_id, 'edd_fields' );
                    }
                    else {
                        update_post_meta( $post_id, 'edd_fields', $new_fields );
                    }
                    
                }
                
            }
            
        }
        
        /**
         * Register our CSS/JS to use later
         * 
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function register_scripts() {
            
            wp_register_style(
                EDD_Fields::$plugin_id . '-admin',
                EDD_Fields_URL . '/admin.css',
                null,
                defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : EDD_Fields_VER
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
            
            $post_types = apply_filters( 'edd_fields_metabox_post_types' , array( 'download' ) );
            
            if ( ( in_array( $current_screen->post_type, $post_types ) ) && ( in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) ) ) {
            
                wp_enqueue_style( EDD_Fields::$plugin_id . '-admin' );
                
            }
            
        }
        
        /**
         * Outputs Download Fields as a table via Shortcode
         * 
         * @access      public
         * @since       1.0.0
         * @return      HTML
         */
        public function table_output( $atts, $content ) {
            
            $atts = shortcode_atts( 
                array(
                    'class' => '',
                    'post_id' => get_the_ID(),
                ), 
                $atts,
                'edd_fields_table'
            );
            
            ob_start();
            
            $repeater = get_post_meta( $atts['post_id'], 'edd_fields', true );
            
            if ( count( $repeater ) > 0 && $repeater !== '' ) : ?>

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
         * Shortcode to grab individual EDD Fields Values
         * 
         * @param       array  $atts    Shortcode Attributes
         * @param       string $content We're not actually using this, but I like to have it there for completeness
         *                                                                                             
         * @access      public
         * @since       1.0.0
         * @return      string
         */
        public function edd_field_shortcode( $atts, $content ) {
            
            $atts = shortcode_atts( 
                array(
                    'name' => '',
                    'post_id' => get_the_ID(),
                ), 
                $atts,
                'edd_field'
            );
            
            if ( $atts['name'] == '' ) {
                return __( 'You must specify a Field Name. Example: [edd_field name="test"]', EDD_Fields::$plugin_id );
            }
            
            return EDD_Fields::get_field( $atts['post_id'], $atts['name'] );
            
        }
        
        /**
         * Static Function to grab an individual EDD Fields value. Useful for Theme Template Files.
         * 
         * @param       integer $post_id    Post ID
         * @param       string  $key        Key 
         *                           
         * @access      public
         * since        1.0.0
         * @return      string Value
         */
        public static function get_field( $post_id, $key ) {
            
            $edd_fields = get_post_meta( $post_id, 'edd_fields', true );
            
            // Collapse into a one-dimensional array of the Keys to find our Index
            $key_list = array_map( function( $array ) {
                return $array['key'];
            }, $edd_fields );
            
            return $edd_fields[ array_search( $key, $key_list ) ]['value'];
            
        }
        
        /**
         * Force our Shortcode to load on Single Downloads
         * @param  string $content The Content
         * @return string The Content
         */
        public function inject_shortcode( $content ) {
            
            if ( is_single() && get_post_type() == 'download' ) {
                $content .= '[edd_fields_table]';
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