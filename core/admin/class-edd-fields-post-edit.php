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
	 * @access	  public
	 * @since	   1.0.0
	 * @return	  void
	 */
	public function add_meta_boxes() {
		
		$post_types = apply_filters( 'edd_fields_metabox_post_types' , array( 'download' ) );

		foreach ( $post_types as $post_type ) {

			$post_type_labels = get_post_type_object( $post_type );
			$post_type_labels = $post_type_labels->labels;

			add_meta_box(
				'edd_fields_meta_box', // Metabox ID
				sprintf( __( '%1$s Fields', EDD_Fields_ID ), $post_type_labels->singular_name, $post_type_labels->name ), // Metabox Label
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
	 * @access	  public
	 * @since	   1.0.0
	 * @return	  void
	 */
	public function fields( $post ) {

		$fields = get_post_meta( $post->ID, 'edd_fields', true );
		$templates = EDDFIELDS()->utility->get_templates();

		ob_start(); ?>

		<div class="edd-fields-meta-box">
			
			<?php 
		
				if ( ! $active_tab = get_post_meta( $post->ID, 'edd_fields_tab', true ) ) {
					$active_tab = count( $templates ); // jQuery UI Tabs are 0 indexed, but PHP is not, so this works
				}
		
			?>
			
			<input type="hidden" name="edd_fields_tab" value="<?php echo $active_tab; ?>"/>
			
			<ul class="edd-fields-tabs">
				<?php foreach ( $templates as $template ) : ?>
					<li><a href="#<?php echo str_replace( ' ', '-', strtolower( $template['label'] ) ); ?>"><span class="<?php echo $template['icon']; ?>"></span></a></li>
				<?php endforeach; ?>
				<li><a href="#custom"><span class="dashicons dashicons-admin-generic"></span></a></li>
			</ul>
			<br class="clear" />
				
				<?php foreach ( $templates as $template ) : ?>
				
					<div class="hidden" id="<?php echo str_replace( ' ', '-', strtolower( $template['label'] ) ); ?>">
						
						<h2><?php echo $template['label']; ?></h2>
						
						<table class="edd-fields-template widefat" width="100%" cellpadding="0" cellspacing="0">
							
							<thead>
								<tr>
									<th scope="col" class="edd-fields-name"><?php _e( 'Name', EDD_Fields_ID ); ?></th>
									<th scope="col" class="edd-fields-value"><?php _e( 'Value', EDD_Fields_ID ); ?></th>
								</tr>
							</thead>
						
						<?php for ( $index = 0; $index < count( $template['fields'] ); $index++ ) : 
						
							$field = $template['fields'][ $index ]; ?>
							
							<tr>
								
								<th class="edd-fields-key">
									<?php echo $field['label']; ?>
									<input type="hidden" name="edd_fields[<?php echo str_replace( ' ', '-', strtolower( $template['label'] ) ); ?>][<?php echo $index; ?>][key]" value="<?php echo $field['label']; ?>" />
								</th>
		
								<td class="edd-fields-value">
									<?php echo EDD()->html->text( array(
										'name' => "edd_fields[" . str_replace( ' ', '-', strtolower( $template['label'] ) ) . "][$index][value]",
										'value' => ( isset( $fields[ str_replace( ' ', '-', strtolower( $template['label'] ) ) ][$index]['value'] ) ) ? $fields[ str_replace( ' ', '-', strtolower( $template['label'] ) ) ][$index]['value'] : '',
									) ); ?>
								</td>
								
							</tr>
						
						<?php endfor; ?>
							
						</table>
						
					</div>
				
				<?php endforeach; ?>
			
				<div id="custom">
					
					<h2><?php echo _x( 'Custom (No Template)', 'No Template Header', EDD_Fields_ID ); ?></h2>

					<table class="edd-fields-repeater widefat edd_repeatable_table" width="100%" cellpadding="0" cellspacing="0">

						<thead>
							<tr>
								<th scope="col" class="edd-repeater-field-handle"></th>
								<th scope="col" class="edd-fields-name"><?php _e( 'Name', EDD_Fields_ID ); ?></th>
								<th scope="col" class="edd-fields-value"><?php _e( 'Value', EDD_Fields_ID ); ?></th>
								<th scope="col"></th>
							</tr>
						</thead>

					<?php if ( ! empty( $fields ) ) : 

						foreach ( $fields['custom'] as $key => $value ) : 

								$name = isset( $value['key'] ) ? $value['key'] : '';
								$value = isset( $value['value'] ) ? $value['value'] : '';
								$args = apply_filters( 'edd_fields_row_args', compact( 'name', 'value' ), $post->ID );

								do_action( 'edd_fields_render_row', $key, $args );

						endforeach;

					else :

						do_action( 'edd_fields_render_row', 0, array() );

					endif; ?>

						<tr>
							<td class="submit" colspan="4" style="float: none; clear:both; background:#fff;">
								<button class="button-secondary edd_add_repeatable" style="margin: 6px 0;"><?php _e( 'Add Field', EDD_Fields_ID ); ?></button>
							</td>
						</tr>

					</table>
					
				</div>
			
		</div>

		<?php wp_nonce_field( basename( __FILE__ ), 'edd_fields_meta_box_nonce' ); ?>

		<?php echo ob_get_clean();

	}

	/**
	 * Function to render each row of our Repeater in the Metabox
	 * 
	 * @param	   integer $key Array Key
	 * @param	   array $args Holds HTML Name and Value
	 *						  
	 * @access	  public
	 * @since	   1.0.0
	 * @return	  void
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
					'name' => "edd_fields[custom][$key][key]",
					'value' => $args['name'],
				) ); ?>
			</td>
			<td class="edd-fields-value">
				<?php echo EDD()->html->text( array(
					'name' => "edd_fields[custom][$key][value]",
					'value' => $args['value'],
				) ); ?>
			</td>
			<td>
				<button class="edd_remove_repeatable" data-type="file" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">
					<span class="screen-reader-text"><?php _e( 'Remove Field', EDD_Fields_ID ); ?></span><span aria-hidden="true">&times;</span>
				</button>
			</td>
		</tr>

		<?php

	}

	/**
	 * Save Our Custom Post Meta
	 * 
	 * @param	   integer $post_id Current Post ID
	 *									  
	 * @access	  public
	 * @since	   1.0.0
	 * @return	  void
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
			
			if ( ! empty( $_POST['edd_fields_tab'] ) ) {
				
				// Sanitization Filter.
				$new_tab = apply_filters( 'edd_metabox_save_fields', $_POST['edd_fields_tab'] );
				
				update_post_meta( $post_id, 'edd_fields_tab', $new_tab );
				
			}

			if ( ! empty( $_POST['edd_fields'] ) ) {

				// Sanitization Filter. Values are already forced to re-index on Save.
				$new_fields = apply_filters( 'edd_metabox_save_fields', $_POST['edd_fields'] );

				// If it is a Custom Template and everything is empty
				update_post_meta( $post_id, 'edd_fields', $new_fields );

			}

		}

	}
	
	/**
	 * Enqueue our CSS/JS on the Admin Side
	 * 
	 * @access	  public
	 * @since	   1.0.0
	 * @return	  void
	 */
	public function admin_enqueue_scripts() {

		$current_screen = get_current_screen();
		global $pagenow;

		$post_types = apply_filters( 'edd_fields_metabox_post_types' , array( 'download' ) );

		if ( ( in_array( $current_screen->post_type, $post_types ) ) && ( in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) ) ) {
			
			wp_enqueue_style( EDD_Fields_ID . '-admin' );
			wp_enqueue_script( EDD_Fields_ID . '-admin' );

		}

	}
	
	/**
	 * You can't enqueue here. It is upsetting.
	 * 
	 * @access	  public
	 * @since	   1.0.0
	 * @return	  void
	 */
	public function force_after_tiny_mce() {

		printf( '<script type="text/javascript" src="%s"></script>',  EDD_Fields_URL . 'assets/js/tinymce/tinymce-select.js' );

	}
	
	/**
	 * Add Our TinyMCE Shortcodes to the Content Editor via a few Filters
	 * 
	 * @access	  public
	 * @since	   1.0.0
	 * @return	  void
	 */
	public function tinymce_shortcodes() {

		if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {

			add_filter( 'mce_buttons', function( $buttons ) {
				array_push( $buttons, 'edd_fields_shortcodes' );
				return $buttons;
			} );

			// Attach script to the button rather than enqueueing it
			add_filter( 'mce_external_plugins', function( $plugin_array ) {
				$plugin_array['edd_fields_shortcodes_script'] = EDD_Fields_URL . 'assets/js/tinymce/edd-fields-shortcodes.js';
				return $plugin_array;
			} );

		}

	}
	
	/**
	 * Grabs all the Post IDs for our Dropdown
	 * 
	 * @access	  public
	 * @since	   1.0.0
	 * @return	  JSON
	 */
	public static function tinymce_shortcode_post_id_ajax() {

		$current_post_type = $_POST['current_post_type'];

		if ( $current_post_type == 'download' ) { // EDD Allows Users to Filter this. Some other plugins do too, but we're targeting EDD
			$singular = edd_get_label_singular();
			$plural = edd_get_label_plural();
		}
		else {
			$post_type_object = get_post_type_object( $current_post_type );
			$singular = $post_type_object->labels->singular_name;
			$plural = $post_type_object->labels->name;
		}

		$post_types = apply_filters( 'edd_fields_metabox_post_types', array( 'download' ) );

		$args = array(
			'numberposts' => -1,
			'orderby' => 'name',
			'order' => 'ASC',
			'post_status' => 'publish',
		);

		$result = array(
			array( 'text' => sprintf( __( 'Current %s', EDD_Fields_ID ), $singular ), 'value' => '' )
		);

		foreach ( $post_types as $post_type ) {

			$args['post_type'] = $post_type;
			$query = new WP_Query( $args );

			if ( $query->have_posts() ) : 

				$grouped_posts = array();
				while ( $query->have_posts() ) : $query->the_post();

					if ( count( $post_types ) > 1 ) {
						// Store later for a <optgroup>
						$grouped_posts[] = array( 'text' => get_the_title(), 'value' => get_the_ID() );
					}
					else {
						$result[] = array( 'text' => get_the_title(), 'value' => get_the_ID() );
					}

				endwhile;

				wp_reset_postdata();

				if ( count( $post_types ) > 1 ) {

					$post_type_object = get_post_type_object( $post_type );
					$plural = $post_type_object->labels->name;

					// Create <optgroup>
					$result[] = array(
						'text' => $plural,
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
	 * @access	  public
	 * @since	   1.0.0
	 * @return	  JSON
	 */
	public static function tinymce_shortcode_field_name_ajax() {

		$post_id = $_POST['post_id'];

		$edd_fields = get_post_meta( $post_id, 'edd_fields', true );

		// Collapse into a one-dimensional array of the Keys to find our Index
		$key_list = array_map( function( $array ) {
			return $array['key'];
		}, $edd_fields );

		$result = array(
			array( 'text' => sprintf( __( 'Choose a Field Name', EDD_Fields_ID ), $singular ), 'value' => '' )
		);

		foreach ( $key_list as $name ) {

			$result[] = array( 'text' => $name, 'value' => $name );

		}

		echo json_encode( $result );

		die();

	}

}