<?php
/**
 * The Post Editing portion of EDD Fields
 *
 * @since 1.0.0
 *
 * @package EDD_Fields
 * @subpackage EDD_Fields/core/admin
 */

defined( 'ABSPATH' ) || die();

class EDD_Fields_Post_Edit {

	/**
	 * EDD_Fields_Post_Edit constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {

		// Add Our Fields Metabox
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// Add Rows to our Repeater
		add_action( 'edd_fields_render_row', array( $this, 'edd_fields_render_row' ), 10, 4 );

		// Save our Metabox Data
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

		// Enqueue CSS/JS on the Post Edit Screen
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Force script load after TinyMCE. WP Doesn't Enqueue TinyMCE correctly, so neither will we
		add_action( 'after_wp_tiny_mce', array( $this, 'force_after_tiny_mce' ) );

		// Add Shortcodes to TinyMCE
		add_action( 'admin_init', array( $this, 'tinymce_shortcodes' ) );

	}

	/**
	 * Add our mutatable Meta Box for EDD Fields
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function add_meta_boxes() {

		$post_types = apply_filters( 'edd_fields_metabox_post_types', array( 'download' ) );

		foreach ( $post_types as $post_type ) {

			$post_type_labels = get_post_type_object( $post_type );
			$post_type_labels = $post_type_labels->labels;

			add_meta_box(
				'edd_fields_meta_box', // Metabox ID
				sprintf( __( '%1$s Fields', 'edd-fields' ), $post_type_labels->singular_name, $post_type_labels->name ), // Metabox Label
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
		
		?>

        <div class="edd-fields-meta-box">

            <label for="edd_fields_table_inject">

				<?php

				$inject_shortcode = EDDFIELDS()->utility->is_shortcode_injected( $post->ID );

				echo EDD()->html->checkbox( array(
					'name'    => 'edd_fields_table_inject',
					'current' => $inject_shortcode,
				) );

				echo _x( 'Show Fields Table?', 'Fields Table Inject Checkbox Label', 'edd-fields' ); ?> <span alt="f223"
                                                                                                              class="edd-help-tip dashicons dashicons-editor-help"
                                                                                                              title="<?php echo _x( '<strong>Show Fields Table</strong>: Automatically include the [edd_fields_table] shortcode above the Purchase Button.', 'Fields Table Inject Tooltip', 'edd-fields' ); ?>"></span>

            </label>

            <br/>

			<?php

			$fields    = edd_fields_get_all_saved_fields( $post->ID );
			$templates = edd_fields_get_templates();

			$templates_select = array();

			foreach ( $templates as $template ) {

				$templates_select[ edd_fields_sanitize_key( $template['label'] ) ] = $template['label'];

			}

			// Place an option for "Custom" right at the top
			$templates_select = array( 'custom' => _x( 'Custom (No Template)', 'Custom Template Label', 'edd-fields' ) ) + $templates_select;

			$active_template = edd_fields_get_chosen_template( $post->ID );

			?>

            <p>
                <strong>
					<?php echo _x( 'Select a Field Template Group', 'Fields Template Label', 'edd-fields' ); ?>
                </strong>
                <span alt="f223" class="edd-help-tip dashicons dashicons-editor-help"
                      title="<?php echo _x( '<strong>Select a Field Template Group</strong>: You can choose a Template created on the EDD Fields Settings Page, or use the Custom setting.', 'Select a Field Template Group Tooltip', 'edd-fields' ); ?>"></span>
            </p>

            <p>

				<?php echo EDD()->html->select( array(
					'options'          => $templates_select,
					'selected'         => $active_template,
					'name'             => 'edd_fields_template',
					'show_option_all'  => false,
					'show_option_none' => false,
				) ); ?>
				
				<a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions&section=edd-fields-settings'); ?>">
					<?php _e( 'Manage Field Template Groups Here', 'edd-fields' ); ?>
				</a>

            </p>

            <div id="edd-fields-custom"
                 class="edd-fields-template<?php echo ( $active_template !== 'custom' ) ? ' hidden' : ''; ?>">

                <table class="edd-fields-repeater widefat edd_repeatable_table" width="100%" cellpadding="0"
                       cellspacing="0">

                    <thead>
                    <tr>
                        <th scope="col" class="edd-rbm-repeater-field-handle"></th>
                        <th scope="col" class="edd-fields-name"><?php _e( 'Name', 'edd-fields' ); ?></th>
                        <th scope="col" class="edd-fields-value"><?php _e( 'Value', 'edd-fields' ); ?></th>
                        <th scope="col"></th>
                    </tr>
                    </thead>
					
					<tbody class="edd-repeatables-wrap">

						<?php if ( ! empty( $fields ) ) :
		
							if ( isset( $fields['custom'] ) &&
							   ! empty( $fields['custom'] ) ) : 

								foreach ( $fields['custom'] as $key => $value ) :

									$name  = isset( $value['key'] ) ? $value['key'] : '';
									$value = isset( $value['value'] ) ? $value['value'] : '';
									$args  = apply_filters( 'edd_fields_row_args', compact( 'name', 'value' ), $post->ID );

									do_action( 'edd_fields_render_row', $key, $args );

								endforeach;

							else : 

									do_action( 'edd_fields_render_row', 0, array() );

							endif;

						else :

							do_action( 'edd_fields_render_row', 0, array() );

						endif; ?>

						<tr>
							<td class="submit" colspan="4" style="float: none; clear:both; background:#fff;">
								<button class="button-secondary edd_add_repeatable"
										style="margin: 6px 0;"><?php _e( 'Add Field', 'edd-fields' ); ?></button>
							</td>
						</tr>
						
					</tbody>

                </table>

            </div>
			<?php
			foreach ( $templates as $template ) {

				echo edd_fields_output_fields_input_table(
					$template,
                    $fields,
					$active_template != edd_fields_sanitize_key( $template['label'] )
				);
			}
			?>

        </div>

		<?php wp_nonce_field( basename( __FILE__ ), 'edd_fields_meta_box_nonce' );

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
			'name'  => '',
			'value' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		?>

        <tr class="edd_variable_prices_wrapper edd_repeatable_row" data-key="<?php echo esc_attr( $key ); ?>">
            <td>
                <span class="edd_draghandle edd-draghandle-anchor edd-fields-draghandle-anchor<?php echo ( version_compare( EDD_VERSION, '2.8' ) >= 0 ) ? ' dashicons dashicons-move' : ''; ?>"></span>
            </td>
            <td class="edd-fields-key">
				<?php echo EDD()->html->text( array(
					'name'  => "edd_fields[custom][$key][key]",
					'value' => $args['name'],
				) ); ?>
            </td>
            <td class="edd-fields-value">
				<?php echo EDD()->html->text( array(
					'name'  => "edd_fields[custom][$key][value]",
					'value' => $args['value'],
				) ); ?>
            </td>
            <td>
                <button class="edd_remove_repeatable" data-type="file"
                        style="background: url(<?php echo admin_url( '/images/xit.gif' ); ?>) no-repeat;">
                    <span class="screen-reader-text"><?php _e( 'Remove Field', 'edd-fields' ); ?></span><span
                            aria-hidden="true">&times;</span>
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

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return;
		}

		if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
			return;
		}

		if ( ! current_user_can( 'edit_product', $post_id ) ) {
			return;
		}

		$post_types = apply_filters( 'edd_fields_metabox_post_types', array( 'download' ) );

		if ( in_array( $post->post_type, $post_types ) ) {

			if ( isset( $_POST['edd_fields_table_inject'] ) ) {
				update_post_meta( $post_id, 'edd_fields_table_inject', 'checked' );
			} else {
				update_post_meta( $post_id, 'edd_fields_table_inject', 'unchecked' );
			}

			if ( isset( $_POST['edd_fields_template'] ) ) {

				// Sanitization Filter.
				$new_tab = apply_filters( 'edd_metabox_save_fields', $_POST['edd_fields_template'] );

				update_post_meta( $post_id, 'edd_fields_template', $new_tab );

			}

			if ( ! empty( $_POST['edd_fields'] ) ) {

				// Sanitization Filter. Values are already forced to re-index on Save.
				$new_fields = apply_filters( 'edd_metabox_save_fields', $_POST['edd_fields'] );

				// If it is a Custom Template and everything is empty
				$result = update_post_meta( $post_id, 'edd_fields', $new_fields );

			}

		}

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

		$post_types = apply_filters( 'edd_fields_metabox_post_types', array( 'download' ) );

		if ( ( in_array( $current_screen->post_type, $post_types ) ) && ( in_array( $pagenow, array(
				'post-new.php',
				'post.php'
			) ) )
		) {

			wp_enqueue_style( 'edd-fields-admin' );
			wp_enqueue_script( 'edd-fields-admin' );

		}

	}

	/**
	 * You can't enqueue here. It is upsetting.
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function force_after_tiny_mce() {

		printf( '<script type="text/javascript" src="%s"></script>', EDD_Fields_URL . 'assets/js/tinymce/tinymce-select.js' );

	}

	/**
	 * Add Our TinyMCE Shortcodes to the Content Editor via a few Filters
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      void
	 */
	public function tinymce_shortcodes() {

		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {

			add_filter( 'mce_buttons', function ( $buttons ) {
				array_push( $buttons, 'edd_fields_shortcodes' );

				return $buttons;
			} );

			// Attach script to the button rather than enqueueing it
			add_filter( 'mce_external_plugins', function ( $plugin_array ) {
				$plugin_array['edd_fields_shortcodes_script'] = EDD_Fields_URL . 'assets/js/tinymce/edd-fields-shortcodes.js';

				return $plugin_array;
			} );

		}

	}

	/**
	 * Grabs all the Post IDs for our Dropdown
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      JSON
	 */
	public static function tinymce_shortcode_post_id_ajax() {

		$current_post_type = $_POST['current_post_type'];

		if ( $current_post_type == 'download' ) { // EDD Allows Users to Filter this. Some other plugins do too, but we're targeting EDD
			$singular = edd_get_label_singular();
			$plural   = edd_get_label_plural();
		} else {
			$post_type_object = get_post_type_object( $current_post_type );
			$singular         = $post_type_object->labels->singular_name;
			$plural           = $post_type_object->labels->name;
		}

		$post_types = apply_filters( 'edd_fields_metabox_post_types', array( 'download' ) );

		$args = array(
			'posts_per_page' => - 1,
			'orderby'        => 'name',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		);

		$result = array(
			array( 'text' => sprintf( __( 'Current %s', 'edd-fields' ), $singular ), 'value' => '' )
		);

		foreach ( $post_types as $post_type ) {

			$args['post_type'] = $post_type;
			$query             = new WP_Query( $args );

			if ( $query->have_posts() ) :

				$grouped_posts = array();
				while ( $query->have_posts() ) : $query->the_post();

					if ( count( $post_types ) > 1 ) {
						// Store later for a <optgroup>
						$grouped_posts[] = array( 'text' => get_the_title(), 'value' => get_the_ID() );
					} else {
						$result[] = array( 'text' => get_the_title(), 'value' => get_the_ID() );
					}

				endwhile;

				wp_reset_postdata();

				if ( count( $post_types ) > 1 ) {

					$post_type_object = get_post_type_object( $post_type );
					$plural           = $post_type_object->labels->name;

					// Create <optgroup>
					$result[] = array(
						'text'  => $plural,
						'value' => $grouped_posts,
					);

				}

			endif;

		}

		echo json_encode( $result );

		die();

	}

	/**
	 * Grabs all the Field Names for our Dropdown
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      JSON
	 */
	public static function tinymce_shortcode_field_name_ajax() {

		$post_id = $_POST['post_id'];

		$template = edd_fields_get_chosen_template( $post_id );

		$fields = edd_fields_get_all_saved_fields( $post_id );

		$result = array(
			array( 'text' => sprintf( __( 'Choose a Field Name', 'edd-fields' ), $singular ), 'value' => '' )
		);

		if ( $fields && isset( $fields[ $template ] ) ) {

			$key_list = edd_fields_get_key_list( $fields[ $template ] );

			foreach ( $key_list as $name ) {

				$result[] = array( 'text' => $name, 'value' => $name );
			}
		}

		echo json_encode( $result );

		die();

	}

}