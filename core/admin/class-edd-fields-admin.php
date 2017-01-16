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
	
	/**
	 * Returns the Default Templates if none are saved. This overrides any default values for the Fields
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		array Default Templates
	 */
	public function get_default_templates() {
		
		$music = apply_filters( 'edd_fields_music_template_defaults', array(
			'label' => _x( 'Music', 'Music Template', EDD_Fields_ID ),
			'icon' => 'dashicons dashicons-format-audio',
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
			'icon' => 'dashicons dashicons-editor-code',
			'fields' => array(
				array(
					'label' => _x( 'File Type', 'Software Template: File Type', EDD_Fields_ID ),
				),
			),
		) );
		
		return array_merge( array( $music ), array( $software ) );
		
	}
	
	/**
	 * Returns the Fields used to Generate Field Templates
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		array Fields
	 */
	public function get_template_fields() {
		
		$fields = apply_filters( 'edd_fields_template_fields', array(
			'label' => array(
				'type' => 'text',
				'desc' => _x( 'Template Name', 'Template Name Label', EDD_Fields_ID ),
				'field_class' => '',
				'readonly' => false,
				'std' => '',
			),
			'icon' => array(
				'type' => 'select',
				'desc' => _x( 'Icon', 'Template Tabl Icon Label', EDD_Fields_ID ),
				'field_class' => 'edd-fields-icon',
				'readonly' => false,
				'std' => '',
				'options' => $this->get_dashicons(),
				'chosen' => true,
			),
			'fields' => array(
				'type' => 'fields_repeater',
				'desc' => _x( 'Fields', 'Field Nested Repeater Label', EDD_Fields_ID ),
				'fields' => array(
					'label' => array(
						'type' => 'text',
						'desc' => _x( 'Field Name', 'Field Name Label', EDD_Fields_ID ),
						'field_class' => '',
						'readonly' => false,
						'std' => '',
					),
				),
			),
		) );
		
		return $fields;
		
	}
	
	/**
	 * Returns an Array of Dashicons to use in a Select Dropdown
	 * 
	 * @access		private
	 * @since		1.0.0
	 * @return		array Array of Dashicons
	 */
	private function get_dashicons() {
		
		return array(
			'dashicons dashicons-menu' => 'dashicons-menu',
			'dashicons dashicons-admin-site' => 'dashicons-admin-site',
			'dashicons dashicons-dashboard' => 'dashicons-dashboard',
			'dashicons dashicons-admin-post' => 'dashicons-admin-post',
			'dashicons dashicons-admin-media' => 'dashicons-admin-media',
			'dashicons dashicons-admin-links' => 'dashicons-admin-links',
			'dashicons dashicons-admin-page' => 'dashicons-admin-page',
			'dashicons dashicons-admin-comments' => 'dashicons-admin-comments',
			'dashicons dashicons-admin-appearance' => 'dashicons-admin-appearance',
			'dashicons dashicons-admin-plugins' => 'dashicons-admin-plugins',
			'dashicons dashicons-admin-users' => 'dashicons-admin-users',
			'dashicons dashicons-admin-tools' => 'dashicons-admin-tools',
			'dashicons dashicons-admin-settings' => 'dashicons-admin-settings',
			'dashicons dashicons-admin-network' => 'dashicons-admin-network',
			'dashicons dashicons-admin-home' => 'dashicons-admin-home',
			'dashicons dashicons-admin-generic' => 'dashicons-admin-generic',
			'dashicons dashicons-admin-collapse' => 'dashicons-admin-collapse',
			'dashicons dashicons-filter' => 'dashicons-filter',
			'dashicons dashicons-admin-customizer' => 'dashicons-admin-customizer',
			'dashicons dashicons-admin-multisite' => 'dashicons-admin-multisite',
			'dashicons dashicons-welcome-write-blog' => 'dashicons-welcome-write-blog',
			'dashicons dashicons-welcome-add-page' => 'dashicons-welcome-add-page',
			'dashicons dashicons-welcome-view-site' => 'dashicons-welcome-view-site',
			'dashicons dashicons-welcome-widgets-menus' => 'dashicons-welcome-widgets-menus',
			'dashicons dashicons-welcome-comments' => 'dashicons-welcome-comments',
			'dashicons dashicons-welcome-learn-more' => 'dashicons-welcome-learn-more',
			'dashicons dashicons-format-aside' => 'dashicons-format-aside',
			'dashicons dashicons-format-image' => 'dashicons-format-image',
			'dashicons dashicons-format-gallery' => 'dashicons-format-gallery',
			'dashicons dashicons-format-video' => 'dashicons-format-video',
			'dashicons dashicons-format-status' => 'dashicons-format-status',
			'dashicons dashicons-format-quote' => 'dashicons-format-quote',
			'dashicons dashicons-format-chat' => 'dashicons-format-chat',
			'dashicons dashicons-format-audio' => 'dashicons-format-audio',
			'dashicons dashicons-camera' => 'dashicons-camera',
			'dashicons dashicons-images-alt' => 'dashicons-images-alt',
			'dashicons dashicons-images-alt2' => 'dashicons-images-alt2',
			'dashicons dashicons-video-alt' => 'dashicons-video-alt',
			'dashicons dashicons-video-alt2' => 'dashicons-video-alt2',
			'dashicons dashicons-video-alt3' => 'dashicons-video-alt3',
			'dashicons dashicons-media-archive' => 'dashicons-media-archive',
			'dashicons dashicons-media-audio' => 'dashicons-media-audio',
			'dashicons dashicons-media-code' => 'dashicons-media-code',
			'dashicons dashicons-media-default' => 'dashicons-media-default',
			'dashicons dashicons-media-document' => 'dashicons-media-document',
			'dashicons dashicons-media-interactive' => 'dashicons-media-interactive',
			'dashicons dashicons-media-spreadsheet' => 'dashicons-media-spreadsheet',
			'dashicons dashicons-media-text' => 'dashicons-media-text',
			'dashicons dashicons-media-video' => 'dashicons-media-video',
			'dashicons dashicons-playlist-audio' => 'dashicons-playlist-audio',
			'dashicons dashicons-playlist-video' => 'dashicons-playlist-video',
			'dashicons dashicons-controls-play' => 'dashicons-controls-play',
			'dashicons dashicons-controls-pause' => 'dashicons-controls-pause',
			'dashicons dashicons-controls-forward' => 'dashicons-controls-forward',
			'dashicons dashicons-controls-skipforward' => 'dashicons-controls-skipforward',
			'dashicons dashicons-controls-back' => 'dashicons-controls-back',
			'dashicons dashicons-controls-skipback' => 'dashicons-controls-skipback',
			'dashicons dashicons-controls-repeat' => 'dashicons-controls-repeat',
			'dashicons dashicons-controls-volumeon' => 'dashicons-controls-volumeon',
			'dashicons dashicons-controls-volumeoff' => 'dashicons-controls-volumeoff',
			'dashicons dashicons-image-crop' => 'dashicons-image-crop',
			'dashicons dashicons-image-rotate' => 'dashicons-image-rotate',
			'dashicons dashicons-image-rotate-left' => 'dashicons-image-rotate-left',
			'dashicons dashicons-image-rotate-right' => 'dashicons-image-rotate-right',
			'dashicons dashicons-image-flip-vertical' => 'dashicons-image-flip-vertical',
			'dashicons dashicons-image-flip-horizontal' => 'dashicons-image-flip-horizontal',
			'dashicons dashicons-image-filter' => 'dashicons-image-filter',
			'dashicons dashicons-undo' => 'dashicons-undo',
			'dashicons dashicons-redo' => 'dashicons-redo',
			'dashicons dashicons-editor-bold' => 'dashicons-editor-bold',
			'dashicons dashicons-editor-italic' => 'dashicons-editor-italic',
			'dashicons dashicons-editor-ul' => 'dashicons-editor-ul',
			'dashicons dashicons-editor-ol' => 'dashicons-editor-ol',
			'dashicons dashicons-editor-quote' => 'dashicons-editor-quote',
			'dashicons dashicons-editor-alignleft' => 'dashicons-editor-alignleft',
			'dashicons dashicons-editor-aligncenter' => 'dashicons-editor-aligncenter',
			'dashicons dashicons-editor-alignright' => 'dashicons-editor-alignright',
			'dashicons dashicons-editor-insertmore' => 'dashicons-editor-insertmore',
			'dashicons dashicons-editor-spellcheck' => 'dashicons-editor-spellcheck',
			'dashicons dashicons-editor-expand' => 'dashicons-editor-expand',
			'dashicons dashicons-editor-contract' => 'dashicons-editor-contract',
			'dashicons dashicons-editor-kitchensink' => 'dashicons-editor-kitchensink',
			'dashicons dashicons-editor-underline' => 'dashicons-editor-underline',
			'dashicons dashicons-editor-justify' => 'dashicons-editor-justify',
			'dashicons dashicons-editor-textcolor' => 'dashicons-editor-textcolor',
			'dashicons dashicons-editor-paste-word' => 'dashicons-editor-paste-word',
			'dashicons dashicons-editor-paste-text' => 'dashicons-editor-paste-text',
			'dashicons dashicons-editor-removeformatting' => 'dashicons-editor-removeformatting',
			'dashicons dashicons-editor-video' => 'dashicons-editor-video',
			'dashicons dashicons-editor-customchar' => 'dashicons-editor-customchar',
			'dashicons dashicons-editor-outdent' => 'dashicons-editor-outdent',
			'dashicons dashicons-editor-indent' => 'dashicons-editor-indent',
			'dashicons dashicons-editor-help' => 'dashicons-editor-help',
			'dashicons dashicons-editor-strikethrough' => 'dashicons-editor-strikethrough',
			'dashicons dashicons-editor-unlink' => 'dashicons-editor-unlink',
			'dashicons dashicons-editor-rtl' => 'dashicons-editor-rtl',
			'dashicons dashicons-editor-break' => 'dashicons-editor-break',
			'dashicons dashicons-editor-code' => 'dashicons-editor-code',
			'dashicons dashicons-editor-paragraph' => 'dashicons-editor-paragraph',
			'dashicons dashicons-editor-table' => 'dashicons-editor-table',
			'dashicons dashicons-align-left' => 'dashicons-align-left',
			'dashicons dashicons-align-right' => 'dashicons-align-right',
			'dashicons dashicons-align-center' => 'dashicons-align-center',
			'dashicons dashicons-align-none' => 'dashicons-align-none',
			'dashicons dashicons-lock' => 'dashicons-lock',
			'dashicons dashicons-unlock' => 'dashicons-unlock',
			'dashicons dashicons-calendar' => 'dashicons-calendar',
			'dashicons dashicons-calendar-alt' => 'dashicons-calendar-alt',
			'dashicons dashicons-visibility' => 'dashicons-visibility',
			'dashicons dashicons-hidden' => 'dashicons-hidden',
			'dashicons dashicons-post-status' => 'dashicons-post-status',
			'dashicons dashicons-edit' => 'dashicons-edit',
			'dashicons dashicons-trash' => 'dashicons-trash',
			'dashicons dashicons-sticky' => 'dashicons-sticky',
			'dashicons dashicons-external' => 'dashicons-external',
			'dashicons dashicons-arrow-up' => 'dashicons-arrow-up',
			'dashicons dashicons-arrow-down' => 'dashicons-arrow-down',
			'dashicons dashicons-arrow-right' => 'dashicons-arrow-right',
			'dashicons dashicons-arrow-left' => 'dashicons-arrow-left',
			'dashicons dashicons-arrow-up-alt' => 'dashicons-arrow-up-alt',
			'dashicons dashicons-arrow-down-alt' => 'dashicons-arrow-down-alt',
			'dashicons dashicons-arrow-right-alt' => 'dashicons-arrow-right-alt',
			'dashicons dashicons-arrow-left-alt' => 'dashicons-arrow-left-alt',
			'dashicons dashicons-arrow-up-alt2' => 'dashicons-arrow-up-alt2',
			'dashicons dashicons-arrow-down-alt2' => 'dashicons-arrow-down-alt2',
			'dashicons dashicons-arrow-right-alt2' => 'dashicons-arrow-right-alt2',
			'dashicons dashicons-arrow-left-alt2' => 'dashicons-arrow-left-alt2',
			'dashicons dashicons-sort' => 'dashicons-sort',
			'dashicons dashicons-leftright' => 'dashicons-leftright',
			'dashicons dashicons-randomize' => 'dashicons-randomize',
			'dashicons dashicons-list-view' => 'dashicons-list-view',
			'dashicons dashicons-exerpt-view' => 'dashicons-exerpt-view',
			'dashicons dashicons-grid-view' => 'dashicons-grid-view',
			'dashicons dashicons-move' => 'dashicons-move',
			'dashicons dashicons-share' => 'dashicons-share',
			'dashicons dashicons-share-alt' => 'dashicons-share-alt',
			'dashicons dashicons-share-alt2' => 'dashicons-share-alt2',
			'dashicons dashicons-twitter' => 'dashicons-twitter',
			'dashicons dashicons-rss' => 'dashicons-rss',
			'dashicons dashicons-email' => 'dashicons-email',
			'dashicons dashicons-email-alt' => 'dashicons-email-alt',
			'dashicons dashicons-facebook' => 'dashicons-facebook',
			'dashicons dashicons-facebook-alt' => 'dashicons-facebook-alt',
			'dashicons dashicons-googleplus' => 'dashicons-googleplus',
			'dashicons dashicons-networking' => 'dashicons-networking',
			'dashicons dashicons-hammer' => 'dashicons-hammer',
			'dashicons dashicons-art' => 'dashicons-art',
			'dashicons dashicons-migrate' => 'dashicons-migrate',
			'dashicons dashicons-performance' => 'dashicons-performance',
			'dashicons dashicons-universal-access' => 'dashicons-universal-access',
			'dashicons dashicons-universal-access-alt' => 'dashicons-universal-access-alt',
			'dashicons dashicons-tickets' => 'dashicons-tickets',
			'dashicons dashicons-nametag' => 'dashicons-nametag',
			'dashicons dashicons-clipboard' => 'dashicons-clipboard',
			'dashicons dashicons-heart' => 'dashicons-heart',
			'dashicons dashicons-megaphone' => 'dashicons-megaphone',
			'dashicons dashicons-schedule' => 'dashicons-schedule',
			'dashicons dashicons-wordpress' => 'dashicons-wordpress',
			'dashicons dashicons-wordpress-alt' => 'dashicons-wordpress-alt',
			'dashicons dashicons-pressthis' => 'dashicons-pressthis',
			'dashicons dashicons-update' => 'dashicons-update',
			'dashicons dashicons-screenoptions' => 'dashicons-screenoptions',
			'dashicons dashicons-info' => 'dashicons-info',
			'dashicons dashicons-cart' => 'dashicons-cart',
			'dashicons dashicons-feedback' => 'dashicons-feedback',
			'dashicons dashicons-cloud' => 'dashicons-cloud',
			'dashicons dashicons-translation' => 'dashicons-translation',
			'dashicons dashicons-tag' => 'dashicons-tag',
			'dashicons dashicons-category' => 'dashicons-category',
			'dashicons dashicons-archive' => 'dashicons-archive',
			'dashicons dashicons-tagcloud' => 'dashicons-tagcloud',
			'dashicons dashicons-text' => 'dashicons-text',
			'dashicons dashicons-yes' => 'dashicons-yes',
			'dashicons dashicons-no' => 'dashicons-no',
			'dashicons dashicons-no-alt' => 'dashicons-no-alt',
			'dashicons dashicons-plus' => 'dashicons-plus',
			'dashicons dashicons-plus-alt' => 'dashicons-plus-alt',
			'dashicons dashicons-minus' => 'dashicons-minus',
			'dashicons dashicons-dismiss' => 'dashicons-dismiss',
			'dashicons dashicons-marker' => 'dashicons-marker',
			'dashicons dashicons-star-filled' => 'dashicons-star-filled',
			'dashicons dashicons-star-half' => 'dashicons-star-half',
			'dashicons dashicons-star-empty' => 'dashicons-star-empty',
			'dashicons dashicons-flag' => 'dashicons-flag',
			'dashicons dashicons-warning' => 'dashicons-warning',
			'dashicons dashicons-location' => 'dashicons-location',
			'dashicons dashicons-location-alt' => 'dashicons-location-alt',
			'dashicons dashicons-vault' => 'dashicons-vault',
			'dashicons dashicons-shield' => 'dashicons-shield',
			'dashicons dashicons-shield-alt' => 'dashicons-shield-alt',
			'dashicons dashicons-sos' => 'dashicons-sos',
			'dashicons dashicons-search' => 'dashicons-search',
			'dashicons dashicons-slides' => 'dashicons-slides',
			'dashicons dashicons-analytics' => 'dashicons-analytics',
			'dashicons dashicons-chart-pie' => 'dashicons-chart-pie',
			'dashicons dashicons-chart-bar' => 'dashicons-chart-bar',
			'dashicons dashicons-chart-line' => 'dashicons-chart-line',
			'dashicons dashicons-chart-area' => 'dashicons-chart-area',
			'dashicons dashicons-groups' => 'dashicons-groups',
			'dashicons dashicons-businessman' => 'dashicons-businessman',
			'dashicons dashicons-id' => 'dashicons-id',
			'dashicons dashicons-id-alt' => 'dashicons-id-alt',
			'dashicons dashicons-products' => 'dashicons-products',
			'dashicons dashicons-awards' => 'dashicons-awards',
			'dashicons dashicons-forms' => 'dashicons-forms',
			'dashicons dashicons-testimonial' => 'dashicons-testimonial',
			'dashicons dashicons-portfolio' => 'dashicons-portfolio',
			'dashicons dashicons-book' => 'dashicons-book',
			'dashicons dashicons-book-alt' => 'dashicons-book-alt',
			'dashicons dashicons-download' => 'dashicons-download',
			'dashicons dashicons-upload' => 'dashicons-upload',
			'dashicons dashicons-backup' => 'dashicons-backup',
			'dashicons dashicons-clock' => 'dashicons-clock',
			'dashicons dashicons-lightbulb' => 'dashicons-lightbulb',
			'dashicons dashicons-microphone' => 'dashicons-microphone',
			'dashicons dashicons-desktop' => 'dashicons-desktop',
			'dashicons dashicons-laptop' => 'dashicons-laptop',
			'dashicons dashicons-tablet' => 'dashicons-tablet',
			'dashicons dashicons-smartphone' => 'dashicons-smartphone',
			'dashicons dashicons-phone' => 'dashicons-phone',
			'dashicons dashicons-index-card' => 'dashicons-index-card',
			'dashicons dashicons-carrot' => 'dashicons-carrot',
			'dashicons dashicons-building' => 'dashicons-building',
			'dashicons dashicons-store' => 'dashicons-store',
			'dashicons dashicons-album' => 'dashicons-album',
			'dashicons dashicons-palmtree' => 'dashicons-palmtree',
			'dashicons dashicons-tickets-alt' => 'dashicons-tickets-alt',
			'dashicons dashicons-money' => 'dashicons-money',
			'dashicons dashicons-smiley' => 'dashicons-smiley',
			'dashicons dashicons-thumbs-up' => 'dashicons-thumbs-up',
			'dashicons dashicons-thumbs-down' => 'dashicons-thumbs-down',
			'dashicons dashicons-layout' => 'dashicons-layout',
			'dashicons dashicons-paperclip' => 'dashicons-paperclip',
		);
		
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