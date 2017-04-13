<?php
/*
 * Plugin Name: Easy Digital Downloads - Fields
 * Plugin URL: https://easydigitaldownloads.com/downloads/fields
 * Description: Easily create custom attributes or meta for your Downloads
 * Version: 0.6.3
 * Text Domain: edd-fields
 * Author: Real Big Plugins
 * Author URI: https://realbigplugins.com
 * Contributors: d4mation
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'EDD_Fields' ) ) {

	/**
	 * Main EDD_Fields class
	 *
	 * @since       1.0.0
	 */
	class EDD_Fields {

		/**
		 * @var            array $plugin_data Holds Plugin Header Info
		 * @since        1.0.0
		 */
		private $plugin_data;

		/**
		 * @var            EDD_Fields_Utility $utility Utility Functions
		 * @since        1.0.0
		 */
		public $utility;

		/**
		 * @var            EDD_Fields_Admin $admin Admin Settings
		 * @since        1.0.0
		 */
		public $admin;

		/**
		 * @var            EDD_Fields_Post_Edit $post_edit Post Edit Screen Additions
		 * @since        1.0.0
		 */
		public $post_edit;

		/**
		 * @var            EDD_Fields_Shortcodes $shortcodes Shortcodes
		 * @since        1.0.0
		 */
		public $shortcodes;

		/**
		 * @var            array $admin_errors Stores all our Admin Errors to fire at once
		 * @since        1.0.0
		 */
		private $admin_errors;

		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since      1.0.0
		 * @return      object self::$instance The one true EDD_Fields
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

			if ( defined( 'EDD_VERSION' )
			     && ( version_compare( EDD_VERSION, '2.6.11' ) < 0 )
			) {

				$this->admin_errors[] = sprintf( _x( '%s requires v%s of %s or higher to be installed!', 'Outdated Dependency Error', 'edd-fields' ), '<strong>' . $this->plugin_data['Name'] . '</strong>', '2.6.11', '<a href="//wordpress.org/plugins/easy-digital-downloads/" target="_blank"><strong>Easy Digital Downloads</strong></a>' );

				if ( ! has_action( 'admin_notices', array( $this, 'admin_errors' ) ) ) {
					add_action( 'admin_notices', array( $this, 'admin_errors' ) );
				}

			}

			$this->require_necessities();

			// Register our CSS/JS for the whole plugin
			add_action( 'init', array( $this, 'register_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Include and init any Widgets
			add_action( 'widgets_init', array( $this, 'init_widgets' ) );

			// Handle licensing
			if ( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, $this->plugin_data['Name'], EDD_Fields_VER, $this->plugin_data['Author'] );
			}
		}

		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
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
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function load_textdomain() {

			// Set filter for language directory
			$lang_dir = EDD_Fields_DIR . '/languages/';
			$lang_dir = apply_filters( 'EDD_Fields_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'edd-fields' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'edd-fields', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/edd-fields/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-fields/ folder
				// This way translations can be overridden via the Theme/Child Theme
				load_textdomain( 'edd-fields', $mofile_global );
			} else if ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-fields/languages/ folder
				load_textdomain( 'edd-fields', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-fields', false, $lang_dir );
			}

		}

		/**
		 * Include different aspects of the Plugin
		 *
		 * @access      private
		 * @since       1.0.0
		 * @return      void
		 */
		private function require_necessities() {

			require_once EDD_Fields_DIR . '/core/class-edd-fields-utility.php';
			$this->utility = new EDD_Fields_Utility();

			if ( is_admin() ) {

				require_once EDD_Fields_DIR . '/core/admin/class-edd-fields-admin.php';
				$this->admin = new EDD_Fields_Admin();

				require_once EDD_Fields_DIR . '/core/admin/class-edd-fields-post-edit.php';
				$this->post_edit = new EDD_Fields_Post_Edit();

			} else {

				require_once EDD_Fields_DIR . '/core/front/class-edd-fields-shortcodes.php';
				$this->shortcodes = new EDD_Fields_Shortcodes();

			}

			if ( defined( 'EDD_Compare_Products_VER' ) && version_compare( EDD_Compare_Products_VER, '1.1.2' ) >= 0 ) {

				require_once EDD_Fields_DIR . '/core/integrations/edd-compare-products/class-edd-fields-compare-products.php';

			} else if ( defined( 'EDD_COMPARE_PRODUCTS_VER' ) ) {
				// Before the Constant changed
			}

		}

		/**
		 * Adds custom formbuilder fields.
		 *
		 * @since {{VERSION}}
		 * @access private
		 *
		 * @param array $fields
		 */
		static function add_formbuilder_fields( $fields ) {

			require_once __DIR__ . '/core/admin/class-edd-fields-formbuilder-field.php';

			$fields['edd_fields'] = 'EDD_Fields_FormBuilderField';

			return $fields;
		}

		/**
		 * Show admin errors.
		 *
		 * @access      public
		 * @since      1.0.0
		 * @return      HTML
		 */
		public function admin_errors() {
			?>
            <div class="error">
				<?php foreach ( $this->admin_errors as $notice ) : ?>
                    <p>
						<?php echo $notice; ?>
                    </p>
				<?php endforeach; ?>
            </div>
			<?php
		}

		/**
		 * Register our CSS/JS to use later
		 *
		 * @access        public
		 * @since        1.0.0
		 * @return        void
		 */
		public function register_scripts() {

			wp_register_style(
				EDD_Fields_ID . '-front',
				EDD_Fields_URL . 'assets/css/front.css',
				null,
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : EDD_Fields_VER
			);

			wp_register_style(
				EDD_Fields_ID . '-admin',
				EDD_Fields_URL . 'assets/css/admin.css',
				null,
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : EDD_Fields_VER
			);

			wp_register_script(
				EDD_Fields_ID . '-front',
				EDD_Fields_URL . 'assets/js/front.js',
				array( 'jquery' ),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : EDD_Fields_VER,
				true
			);

			wp_register_script(
				EDD_Fields_ID . '-admin',
				EDD_Fields_URL . 'assets/js/admin.js',
				array( 'jquery', 'jquery-effects-core', 'jquery-effects-highlight', 'jquery-ui-tabs' ),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : EDD_Fields_VER,
				true
			);

			wp_register_script(
				EDD_Fields_ID . '-fes',
				EDD_Fields_URL . 'assets/js/edd-fields-fes.js',
				array( 'jquery' ),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : EDD_Fields_VER,
				true
			);

			wp_localize_script(
				EDD_Fields_ID . '-admin',
				'eddFields',
				apply_filters( 'edd_fields_localize_admin_script', array() )
			);

			wp_localize_script(
				EDD_Fields_ID . '-fes',
				'eddFieldsFES',
				array(
					'i18n' => array(
						'fesFormBuilderDuplicateFields' => __( 'You already have Download Fields in the form.', 'edd-fields' ),
					)
				)
			);

		}

		/**
		 * Enqueues front-end assets.
		 *
		 * @since {{VERSION}}
		 * @access private
		 */
		function enqueue_scripts() {

			wp_enqueue_style( EDD_Fields_ID . '-front' );
			wp_enqueue_script( EDD_Fields_ID . '-front' );
		}

		/**
		 * Include and Register any Widgets
		 *
		 * @access        public
		 * @since        1.0.0
		 * @return        void
		 */
		public function init_widgets() {

			require_once EDD_Fields_DIR . '/core/widgets/class-edd-fields-widget.php';

		}

	}

	add_filter( 'fes_load_fields_array', array( 'EDD_Fields', 'add_formbuilder_fields' ) );

	register_activation_hook( __FILE__, array( 'EDD_Fields_Install', 'install' ) );

} // End Class Exists Check

/**
 * The main function responsible for returning the one true EDD_Fields
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDD_Fields The one true EDD_Fields
 */
add_action( 'plugins_loaded', 'EDD_Fields_load' );
function EDD_Fields_load() {

	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {

		if ( ! class_exists( 'EDD_Extension_Activation' ) ) {
			require_once 'includes/class.extension-activation.php';
		}

		$activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

	} else {

		add_action( 'wp_ajax_edd_fields_get_posts', array( 'EDD_Fields_Post_Edit', 'tinymce_shortcode_post_id_ajax' ) );
		add_action( 'wp_ajax_edd_fields_get_names', array(
			'EDD_Fields_Post_Edit',
			'tinymce_shortcode_field_name_ajax'
		) );

		require_once __DIR__ . '/core/edd-fields-functions.php';
		EDDFIELDS();

	}

}