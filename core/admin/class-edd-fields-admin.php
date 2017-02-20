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
	 * @var		EDD_Fields_Admin $admin_notices Allows Admin Notices to be ran when possible despite our Hook
	 * @since	1.0.0
	 */
	private $admin_notices = array();

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
		
		// Creates the primary Repeater
		add_action( 'edd_edd_fields_template_settings', array( $this, 'edd_fields_templates_field' ) );
		
		// Creates the Fields Repeater in the Modal
		add_action( 'edd_edd_fields_template_fields', array( $this, 'edd_fields_inner_repeater' ) );
		
		// Localize the Admin Script with some PHP values
		add_filter( 'edd_fields_localize_admin_script', array( $this, 'localize_script' ) );

		// Allow Templates to be reset to their Defaults
		add_action( 'init', array( $this, 'reset_field_templates_to_default' ) );

		// Display Admin Notices
		add_action( 'admin_init', array( $this, 'display_admin_notices' ) );

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
				'id' => 'edd_fields_table_inject',
				'type' => 'checkbox',
				'name' => _x( 'Fields Table Display', 'Fields Table Injection Global Label', EDD_Fields_ID ),
				'desc' => sprintf( _x( 'Disable Automatic Display of Fields Table on the %s Page', 'Fields Table Injection Global Checkbox Label', EDD_Fields_ID ), edd_get_label_singular() ),
				'tooltip_title' => _x( 'Fields Table Display', 'Fields Table Injection Global Tooltip Title', EDD_Fields_ID ),
				'tooltip_desc' => sprintf( _x( "By Default, Fields data will automatically be displayed above the purchase button on the %s page. If you don't want the Fields to display or are manually outputting them through your own methods, you should check this box. This can also be overridden on individual %s.", 'Fields Table Injection Global Tooltip Text', EDD_Fields_ID ), edd_get_label_singular(), edd_get_label_plural() ),
			),
			array(
				'id' => 'edd_fields_template_settings',
				'input_name' => 'edd_fields_template_settings',
				'name' => __( 'Field Template Groups', EDD_Fields_ID ),
				'type' => 'hook',
				'classes' => array( 'edd-fields-settings-repeater' ),
				'add_item_text' => __( 'Add Field Template Group', EDD_Fields_ID ),
				'edit_item_text' => __( 'Edit Field Template Group', EDD_Fields_ID ),
				'delete_item_text' => __( 'Remove Field Template Group', EDD_Fields_ID ),
				'save_item_text' => __( 'Save Field Template Group', EDD_Fields_ID ),
				'defaults_name' => 'edd_fields_template_reset_defaults',
				'defaults_text' => _x( 'Reset to Defaults', 'Reset Field Template Groups to Defaults', EDD_Fields_ID ),
				'defaults_confirmation' => _x( 'Are you sure? You will lose all changes made to the Field Template Groups.', 'Reset Field Template Groups Confirmation Dialog', EDD_Fields_ID ),
				'default_title' => __( 'New Field Template Group', EDD_Fields_ID ),
				'std' => edd_fields_get_templates(),
				'fields' => $this->get_template_fields(),
			),
			array(
				'id' => 'fields_reset_defaults',
				'type' => 'hook',
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
	 * Primary Template Repeater
	 * 
	 * @param		array $args EDD Settings API Args
	 *                                      
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function edd_fields_templates_field( $args ) {
		
		global $edd_options;
		
		$args = wp_parse_args( $args, array(
			'id' => '',
			'std' => '',
			'classes' => array(),
			'fields' => array(),
			'add_item_text' => __( 'Add Row', EDD_Fields_ID ),
			'edit_item_text' => __( 'Edit Row', EDD_Fields_ID ),
			'save_item_text' => __( 'Save Row', EDD_Fields_ID ),
			'delete_item_text' => __( 'Delete Row', EDD_Fields_ID ),
			'default_title' => __( 'New Row', EDD_Fields_ID ),
			'input_name' => false,
			'defaults_name' => 'edd_fields_repeater_reset_defaults',
			'defaults_text' => _x( 'Reset to Defaults', 'Reset Repeater to Defaults', EDD_Fields_ID ),
			'defaults_confirmation' => _x( 'Are you sure? You will lose all changes made to the Repeater.', 'Reset Repeater Confirmation Dialog', EDD_Fields_ID ),
		) );
		
		// We need to grab values this way to ensure Nested Repeaters work
		if ( isset( $edd_options[ $args['id'] ] ) || $args['std'] == '' ) {
			$edd_option = $edd_options[ $args['id'] ];
		}
		else {
			$edd_option = $args['std'];
		}
		
		// Ensure Dummy Field is created
		$field_count = ( count( $args['std'] ) >= 1 ) ? count( $args['std'] ) : 1;
		
		$name = $args['input_name'] !== false ? $args['input_name'] : 'edd_settings[' . esc_attr( $args['id'] ) . ']';
		
		do_action( 'edd_fields_before_templates_repeater' );
		
		?>

		<div data-edd-rbm-repeater class="edd-rbm-repeater <?php echo ( isset( $args['classes'] ) ) ? ' ' . implode( ' ', $args['classes'] ) : ''; ?>">
			
			<div data-repeater-list="<?php echo $name; ?>" class="edd-rbm-repeater-list">

					<?php for ( $index = 0; $index < $field_count; $index++ ) : $value = ( isset( $edd_option[$index] ) ) ? $edd_option[$index] : array(); ?>
				
						<div data-repeater-item<?php echo ( ! isset( $args['std'][$index] ) ) ? ' data-repeater-dummy style="display: none;"' : ''; ?> class="edd-rbm-repeater-item">
							
							<table class="repeater-header wp-list-table widefat fixed posts">

								<thead>

									<tr>
										<th scope="col">
											
											<span class="edd_draghandle" data-repeater-item-handle></span>
											
											<div class="title" data-repeater-default-title="<?php echo $args['default_title']; ?>">

												<?php if ( isset( $edd_option[$index] ) && reset( $edd_option[$index] ) !== '' ) : 
													// Surprisingly, this is the most efficient way to do this. http://stackoverflow.com/a/21219594
													foreach ( $value as $key => $setting ) : ?>
														<?php echo $setting; ?>
													<?php 
														break;
													endforeach; 
												else: ?>

													<?php echo $args['default_title']; ?>

												<?php endif; ?>

											</div>
											
											<div class="edd-rbm-repeater-controls">
											
												<input data-repeater-edit type="button" class="button" value="<?php echo $args['edit_item_text']; ?>" />
												<input data-repeater-delete type="button" class="button button-danger" value="<?php echo $args['delete_item_text']; ?>" />
												
											</div>

										</th>

									</tr>

								</thead>

							</table>
							
							<div class="edd-rbm-repeater-content reveal" data-reveal data-v-offset="64">
								
								<div class="edd-rbm-repeater-form">

									<table class="widefat" width="100%" cellpadding="0" cellspacing="0">

										<tbody>

											<?php foreach ( $args['fields'] as $field_id => $field ) : ?>

												<tr>

													<?php if ( is_callable( "edd_{$field['type']}_callback" ) ) : 
		
														// EDD Generates the Name Attr based on ID, so this nasty workaround is necessary
														$field['id'] = $field_id;
														$field['std'] = ( isset( $value[ $field_id ] ) ) ? $value[ $field_id ] : $field['std'];
		
														if ( $field['type'] == 'checkbox' ) : 
		
															if ( isset( $field['std'] ) && (int) $field['std'] !== 0 ) {
																$field['field_class'][] = 'default-checked';
															}
		
														endif;
		
														if ( $field['type'] !== 'hook' ) : ?>

															<td>

																<?php call_user_func( "edd_{$field['type']}_callback", $field ); ?>

															</td>

														<?php else : 
		
															// Don't wrap calls for a Hook
															call_user_func( "edd_{$field['type']}_callback", $field ); 
		
														endif;
		
													endif; ?>

												</tr>

											<?php endforeach; ?>

										</tbody>

									</table>
									
									<input type="submit" class="button button-primary alignright" value="<?php echo $args['save_item_text']; ?>" />
								  
								</div>
								
								<a class="close-button" data-close aria-label="<?php echo _x( 'Close Notification Editor', 'Close Fields Notification Modal', EDD_Fields_ID ); ?>">
									<span aria-hidden="true">&times;</span>
								</a>
								
							</div>
							
						</div>

					<?php endfor; ?>	  

			</div>
			
			<input data-repeater-create type="button" class="button" style="margin-top: 6px;" value="<?php echo $args['add_item_text']; ?>" />
			
			<input type="submit" name="<?php echo $args['defaults_name']; ?>" class="button button-danger edd-repeater-defaults" style="margin-top: 6px;" value="<?php echo $args['defaults_text']; ?>" onclick="return confirm( '<?php echo $args['defaults_confirmation']; ?>' );" />

		</div>
		
		<?php
		
		do_action( 'edd_fields_after_templates_repeater' );
		
	}
	
	public function edd_fields_inner_repeater( $args ) {
		
		global $edd_options;
		
		$args = wp_parse_args( $args, array(
			'id' => '',
			'std' => '',
			'classes' => array(),
			'fields' => array(),
			'add_item_text' => __( 'Add Row', EDD_Fields_ID ),
			'input_name' => false,
		) );
		
		// We need to grab values this way to ensure Nested Repeaters work
		if ( isset( $edd_options[ $args['id'] ] ) || $args['std'] == '' ) {
			$edd_option = $edd_options[ $args['id'] ];
		}
		else {
			$edd_option = $args['std'];
		}
		
		// Ensure Dummy Field is created
		$field_count = ( count( $edd_option ) >= 1 ) ? count( $edd_option ) : 1;
		
		$name = $args['input_name'] !== false ? $args['input_name'] : 'edd_settings[' . esc_attr( $args['id'] ) . ']';
		
		?>

		<td data-edd-rbm-nested-repeater class="edd-rbm-repeater edd-rbm-nested-repeater edd_meta_table_wrap<?php echo ( isset( $args['classes'] ) ) ? ' ' . implode( ' ', $args['classes'] ) : ''; ?>">
				
			<label for="<?php echo $args['id']; ?>"><?php echo $args['desc']; ?></label>
			
			<div data-repeater-list="<?php echo $args['id']; ?>" class="edd-rbm-repeater-list">
				
				<?php for ( $index = 0; $index < $field_count; $index++ ) : 
		
					$value = ( isset( $edd_option[$index] ) ) ? $edd_option[$index] : array(); ?>
				
						<div data-repeater-item<?php echo ( ! isset( $edd_option[$index] ) ) ? ' data-repeater-dummy style="display: none;"' : ''; ?> class="edd-rbm-repeater-item">
							
							<span class="edd_draghandle" data-repeater-item-handle></span>
							
							<div class="edd-rbm-nested-repeater-fields">

								<?php foreach ( $args['fields'] as $field_id => $field ) : 

									if ( is_callable( "edd_{$field['type']}_callback" ) ) : 

										// EDD Generates the Name Attr based on ID, so this nasty workaround is necessary
										$field['id'] = $field_id;
										$field['std'] = ( isset( $value[ $field_id ] ) ) ? $value[ $field_id ] : $field['std'];

										call_user_func( "edd_{$field['type']}_callback", $field );

									endif;

								endforeach; ?>
								
							</div>
							
							<span class="screen-reader-text"><?php echo $args['delete_item_text']; ?></span>
							<input data-repeater-delete type="button" class="edd_remove_repeatable" data-type="file" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;" />

						</div>
				
				<?php endfor; ?>

			</div>
			
			<input data-repeater-create type="button" class="button" style="margin-top: 6px;" value="<?php echo $args['add_item_text']; ?>" />

		</td>

	<?php
		
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
				'placeholder' => __( 'New Field Template Group', EDD_Fields_ID ),
				'readonly' => false,
				'std' => '',
				'tooltip_title' => _x( 'Template Name', 'Template Name Tooltip Title', EDD_Fields_ID ),
				'tooltip_desc'  => sprintf( _x( 'Controls the Title shown after selecting a Template Tab on the %s Edit Screen.', 'Template Icon Tooltip Text', EDD_Fields_ID ), edd_get_label_singular() ),
			),
			'edd_fields_template_fields' => array(
				'type' => 'hook',
				'desc' => _x( 'Fields', 'Field Nested Repeater Label', EDD_Fields_ID ),
				'add_item_text' => __( 'Add Field', EDD_Fields_ID ),
				'delete_item_text' => __( 'Remove Field', EDD_Fields_ID ),
				'fields' => array(
					'label' => array(
						'type' => 'text',
						'desc' => _x( 'Field Name', 'Field Name Label', EDD_Fields_ID ),
						'placeholder' => '',
						'field_class' => '',
						'readonly' => false,
						'std' => '',
						'tooltip_title' => _x( 'Field Name', 'Field Name Tooltip Title', EDD_Fields_ID ),
						'tooltip_desc'  => sprintf( _x( 'Controls the &ldquo;Name&rdquo; shown for the Field. &ldquo;Value&rdquo; is defined on the %s Edit Scren per %s.', 'Template Icon Tooltip Text', EDD_Fields_ID ), edd_get_label_singular(), edd_get_label_singular() ),
					),
				),
			),
		) );

		return $fields;

	}

	/**
     * Reset Field Template Groups to Default by deleting the saved values
     * 
     * @access		public
     * @since		1.0.0
     * @return		void
     */
	public function reset_field_templates_to_default() {

		// For some reason we can't hook into admin_init within a production environment. Yeah, I have no idea either
		if ( is_admin() ) {

			// If we're reseting to defaults
			if ( isset( $_POST['edd_fields_template_reset_defaults'] ) ) {

				edd_delete_option( 'edd_fields_template_settings' );
				
				$this->admin_notices[] = array(
                    'edd-notices',
                    'edd_fields_template_reset_defaults',
                    _x( 'Field Template Groups Reset to Defaults.', 'Field Template Groups Reset to Defaults Successful', EDD_Fields_ID ),
                    'updated'
                );

			}

		}

	}

	/**
     * Sometimes we need to add Admin Notices when add_settings_error() isn't accessable yet
     * 
     * @access      public
     * @since       1.0.0
     * @return      void
     */
	public function display_admin_notices() {

		foreach( $this->admin_notices as $admin_notice ) {

			// Pass array as Function Parameters
			call_user_func_array( 'add_settings_error', $admin_notice );

		}

		// Clear out Notices
		$this->admin_notices = array();

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
		
		// Dependencies
		wp_enqueue_script( 'jquery-effects-core' );
		wp_enqueue_script( 'jquery-effects-highlight' );
		
		wp_enqueue_script( EDD_Fields_ID . '-admin' );

	}
	
	/**
	 * Localize the Admin.js with some values from PHP-land
	 * 
	 * @param	  array $localization Array holding all our Localizations
	 *														
	 * @access	  public
	 * @since	  1.0.0
	 * @return	  array Modified Array
	 */
	public function localize_script( $localization ) {
		
		$localization['i18n'] = array(
			'activeText' => _x( 'Active Template', 'Active Template Aria Label', EDD_Fields_ID ),
			'inactiveText' => _x( 'Inactive Template', 'Inactive Template Aria Label', EDD_Fields_ID ),
			'confirmDeletion' => _x( 'Are you sure you want to delete this Field Template Group?', 'Confirm Template Deletion', EDD_Fields_ID ),
			'validationError' => _x( 'This field is required', 'Required Field not filled out (Ancient/Bad Browsers Only)', EDD_Fields_ID ),
		);
		
		$localization['url'] = EDD_Fields_URL;
		
		$localization['ajax'] = admin_url( 'admin-ajax.php' );
		
		return $localization;
		
	}
	
	/**
	 * Inserts/Updates a Template via AJAX by Index
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		JSON
	 */
	public static function insert_template() {
		
		if ( is_admin() && current_user_can( 'manage_shop_settings' ) ) {
			
			$index = $_POST['index'];
			
			// We don't want to save this data
			unset( $_POST['index'] );
			unset( $_POST['action'] );
			
			// JavaScript likes to alphabetically arrange Object Members. We're forcing this to the end
			$fields = $_POST['edd_fields_template_fields' ];
			unset( $_POST['edd_fields_template_fields'] );
			$_POST['edd_fields_template_fields'] = $fields;
			
			$edd_fields_options = edd_get_option( 'edd_fields_template_settings' );
			
			$edd_fields_options[ $index ] = $_POST;
			
			$success = edd_update_option( 'edd_fields_template_settings', $edd_fields_options );
			
			if ( $success ) {
				return wp_send_json_success();
			}
			else {
				return wp_send_json_error();
			}
			
		}
		
		return wp_send_json_error( array(
			'error' => _x( 'Access Denied', 'Current User Cannot Insert Templates Error', EDD_Fields_ID ),
		) );
		
	}
	
	/**
	 * Save a fresh copy of the Templates with their new Indexes
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		JSON
	 */
	public static function sort_templates() {
		
		if ( is_admin() && current_user_can( 'manage_shop_settings' ) ) {
			
			foreach ( $_POST['templates'] as &$template ) {
				
				// JavaScript likes to alphabetically arrange Object Members. We're forcing this to the end
				$fields = $template['edd_fields_template_fields' ];
				unset( $template['edd_fields_template_fields'] );
				$template['edd_fields_template_fields'] = $fields;
				
			}
			
			$success = edd_update_option( 'edd_fields_template_settings', $_POST['templates'] );
			
			if ( $success ) {
				return wp_send_json_success();
			}
			else {
				return wp_send_json_error();
			}
			
		}
		
		return wp_send_json_error( array(
			'error' => _x( 'Access Denied', 'Current User Cannot Delete Templates Error', EDD_Fields_ID ),
		) );
		
	}
	
	/**
	 * Deletes a Template via AJAX and reindex the Array
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		JSON
	 */
	public static function delete_template() {
		
		if ( is_admin() && current_user_can( 'manage_shop_settings' ) ) {
			
			$index = $_POST['index'];
			
			$edd_fields_options = edd_get_option( 'edd_fields_template_settings' );
			
			unset( $edd_fields_options[ $index ] );
			
			$success = edd_update_option( 'edd_fields_template_settings', array_values( $edd_fields_options ) );
			
			if ( $success ) {
				return wp_send_json_success();
			}
			else {
				return wp_send_json_error();
			}
			
		}
		
		return wp_send_json_error( array(
			'error' => _x( 'Access Denied', 'Current User Cannot Delete Templates Error', EDD_Fields_ID ),
		) );
		
	}

}

// AJAX Hook for Creating/Updating Templates
add_action( 'wp_ajax_insert_edd_fields_template', array( 'EDD_Fields_Admin', 'insert_template' ) );

// AJAX Hook for Sorting Templates
add_action( 'wp_ajax_sort_edd_fields_templates', array( 'EDD_Fields_Admin', 'sort_templates' ) );

// AJAX Hook for Deleting Templates
add_action( 'wp_ajax_delete_edd_rbm_fields_template', array( 'EDD_Fields_Admin', 'delete_template' ) );