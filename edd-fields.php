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
	 * @since	   1.0.0
	 */
	class EDD_Fields {
		
		/**
		 * @var		 EDD_Fields $plugin_data Holds Plugin Header Info
		 * @since	   1.0.0
		 */
		private $plugin_data;
		
		/**
		 * @var		 EDD_Fields $admin Admin Settings
		 * @since	   1.0.0
		 */
		public $admin;
		
		/**
		 * @var		 EDD_Fields $post_edit Post Edit Screen Additions
		 * @since	   1.0.0
		 */
		public $post_edit;
		
		/**
		 * @var		 EDD_Fields $shortcodes Shortcodes
		 * @since	   1.0.0
		 */
		public $shortcodes;

		/**
		 * Get active instance
		 *
		 * @access	  public
		 * @since	   1.0.0
		 * @return	  object self::$instance The one true EDD_Fields
		 */
		public static function instance() {
			
			static $instance = null;
			
			if ( null === $instance ) {
				$instance = new static();
			}
			
			return $instance;

		}
		
		protected function __construct() {
			
			$this->setup_constants();
			$this->load_textdomain();
			$this->require_necessities();
			
			// Register our CSS/JS for the whole plugin
			add_action( 'init', array( $this, 'register_scripts' ) );
			
			// Handle licensing
			if ( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, $this->plugin_data['Name'], EDD_Fields_VER, $this->plugin_data['Author'] );
			}
			
		}

		/**
		 * Setup plugin constants
		 *
		 * @access	  private
		 * @since	   1.0.0
		 * @return	  void
		 */
		private function setup_constants() {
			
			// WP Loads things so weird. I really want this function.
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}
			
			// Only call this once, accessible always
			$this->plugin_data = get_plugin_data( __FILE__ );
			
			if ( ! defined( 'EDD_Fields_ID' ) ) {
				// Plugin Text Domain
				define( 'EDD_Fields_ID', $this->plugin_data['TextDomain'] );
			}
			
			if ( ! defined( 'EDD_Fields_VER' ) ) {
				// Plugin version
				define( 'EDD_Fields_VER', $this->plugin_data['Version'] );
			}

			if ( ! defined( 'EDD_Fields_DIR' ) ) {
				// Plugin path
				define( 'EDD_Fields_DIR', plugin_dir_path( __FILE__ ) );
			}

			if ( ! defined( 'EDD_Fields_URL' ) ) {
				// Plugin URL
				define( 'EDD_Fields_URL', plugin_dir_url( __FILE__ ) );
			}

		}

		/**
		 * Internationalization
		 *
		 * @access	  private 
		 * @since	   1.0.0
		 * @return	  void
		 */
		private function load_textdomain() {

			// Set filter for language directory
			$lang_dir = EDD_Fields_DIR . '/languages/';
			$lang_dir = apply_filters( 'EDD_Fields_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), EDD_Fields_ID );
			$mofile = sprintf( '%1$s-%2$s.mo', EDD_Fields_ID, $locale );

			// Setup paths to current locale file
			$mofile_local   = $lang_dir . $mofile;
			$mofile_global  = WP_LANG_DIR . '/' . EDD_Fields_ID . '/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-fields/ folder
				// This way translations can be overridden via the Theme/Child Theme
				load_textdomain( EDD_Fields_ID, $mofile_global );
			}
			else if ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-fields/languages/ folder
				load_textdomain( EDD_Fields_ID, $mofile_local );
			}
			else {
				// Load the default language files
				load_plugin_textdomain( EDD_Fields_ID, false, $lang_dir );
			}

		}
		
		/**
		 * Include different aspects of the Plugin
		 * 
		 * @access	  private
		 * @since	   1.0.0
		 * @return	  void
		 */
		private function require_necessities() {
			
			if ( is_admin() ) {
				
				require_once EDD_Fields_DIR . '/core/admin/class-edd-fields-admin.php';
				$this->admin = new EDD_Fields_Admin();
				
				require_once EDD_Fields_DIR . '/core/admin/class-edd-fields-post-edit.php';
				$this->post_edit = new EDD_Fields_Post_Edit();
				
			}
			else {
				
				require_once EDD_Fields_DIR . '/core/front/class-edd-fields-shortcodes.php';
				$this->shortcodes = new EDD_Fields_Shortcodes();
				
			}
			
		}
		
		/**
		 * Register our CSS/JS to use later
		 * 
		 * @access	  public
		 * @since	   1.0.0
		 * @return	  void
		 */
		public function register_scripts() {
			
			wp_register_style(
				EDD_Fields_ID . '-admin',
				EDD_Fields_URL . 'assets/css/admin.css',
				null,
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : EDD_Fields_VER
			);
			
			wp_register_script(
				EDD_Fields_ID . '-admin',
				EDD_Fields_URL . 'assets/js/admin.js',
				array( 'jquery', 'jquery-ui-tabs' ),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : EDD_Fields_VER,
				true
			);
			
		}

	}

} // End Class Exists Check

/**
 * The main function responsible for returning the one true EDD_Fields
 * instance to functions everywhere
 *
 * @since	   1.0.0
 * @return	  \EDD_Fields The one true EDD_Fields
 */
add_action( 'plugins_loaded', 'EDD_Fields_load' );
function EDD_Fields_load() {

	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {

		if ( ! class_exists( 'EDD_Extension_Activation' ) ) {
			require_once 'includes/class.extension-activation.php';
		}

		$activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

	}
	else {
		
		add_action( 'wp_ajax_edd_fields_get_posts', array( 'EDD_Fields_Post_Edit', 'tinymce_shortcode_post_id_ajax' ) );
		add_action( 'wp_ajax_edd_fields_get_names', array( 'EDD_Fields_Post_Edit', 'tinymce_shortcode_field_name_ajax' ) );
		
		require_once __DIR__ . '/core/edd-fields-functions.php';
		EDDFIELDS();
		
	}

}