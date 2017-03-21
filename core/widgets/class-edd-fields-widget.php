<?php
/**
 * The Widget for EDD Fields
 *
 * @since 1.0.0
 *
 * @package EDD_Fields
 * @subpackage EDD_Fields/core/widgets
 */

defined( 'ABSPATH' ) || die();

class EDD_Fields_Widget extends WP_Widget {

	/**
	 * EDD_Fields_Widget constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		
		parent::__construct(
            'edd_fields_widget', // Base ID
            _x( 'EDD Fields', 'EDD Fields Widget Name', EDD_Fields_ID ), // Name
            array( 
                'classname' => 'edd-fields-widget',
                'description' => _x( 'A Widget that can show a Table of all EDD Fields (For the chosen Field Template Group) or an individual Field by Name.', 'EDD Fields Widget Description', EDD_Fields_ID ),
            ) // Args
        );
		
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customizer_styles' ) );

	}
	
	/**
	 * Front-end display of widget
	 * 
	 * @see WP_Widget::widget()
	 * 
	 * @param		array $args     Widget arguments
	 * @param		array $instance Saved values from database
	 *                                           
	 * @access		public
	 * @since		1.0.0
	 * @return		HTML
	 */
	public function widget( $args, $instance ) {
		
		$instance = wp_parse_args( $instance, array(
			'post_id' => 0,
			'shortcode' => 'table',
		) );
		
		$post_id = $instance['post_id'];
			
		// Determine whether or not we're going to build the Shortcode with a Post ID Attribute
		if ( (int) $post_id == 0 ) {
			$post_id = get_the_ID();
			$post_id_string = ' ';
		}
		else {
			$post_id_string = ' post_id="' . $post_id . '" ';
		}
		
		if ( $instance['shortcode'] == 'table' ) {
			
			echo do_shortcode( '[edd_fields_table' . $post_id_string . 'class="edd-fields-table-widget"]' );
			
		}
		else {
			
			$field_name = $instance['field'];
			$fields = get_post_meta( $post_id, 'edd_fields', true );
			$prefix = '';

			// If there's no Fields saved, don't bother continuing
			if ( $fields ) {

				$template = get_post_meta( $post_id, 'edd_fields_template', true );

				if ( isset( $fields[ $template ] ) ) {

					$fields = $fields[ $template ];

					foreach ( $fields as $field ) {

						if ( edd_fields_sanitize_key( $field['key'] ) == $field_name ) {
							$prefix = $field['key'] . ': ';
						}

					}

				}

			}
			
			// Determine whether or not we're going to build the Shortcode with a Name Attribute
			if ( empty( $field_name ) ) {
				echo _x( 'You must choose a Field from the Drop-down in the EDD Fields Widget Settings', 'No Field Chosen in Widget', EDD_Fields_ID );
			}
			else {
				
				$field = edd_fields_get( $field_name, $post_id );
				
				if ( empty( $field ) ) {
					echo $prefix . _x( 'No data found', 'No data found for the chosen Field in the Widget', EDD_Fields_ID );
				}
				else {
					echo $prefix . $field;
				}
				
			}
			
		}
		
	}
	
	/**
	 * Back-end widget form
	 * 
	 * @see WP_Widget::form()
	 * 
	 * @param		array $instance Previously saved values from database
	 *                                                      
	 * @access		public
	 * @since		1.0.0
	 * @return		HTML
	 */
	public function form( $instance ) {
		
		// Previously saved Values
		$saved_post_id = ! empty( $instance['post_id'] ) ? $instance['post_id'] : 0;
		$saved_shortcode = ! empty( $instance['shortcode'] ) ? $instance['shortcode'] : 'table';
		$saved_field = ! empty( $instance['field'] ) ? $instance['field'] : 0;
		
		$post_types = apply_filters( 'edd_fields_metabox_post_types' , array( 'download' ) );
		
		$args = array(
			'posts_per_page' => -1,
			'orderby' => 'name',
			'order' => 'ASC',
			'post_status' => 'publish',
		);
		
		if ( count( $post_types ) == 1 &&
			$post_types[0] == 'download' ) { // EDD Allows Users to Filter this. Some other plugins do too, but we're targeting EDD
			$singular = edd_get_label_singular();
		}
		else {
			$singular = _x( 'Item', 'Current Item Replacement Text for Widget', EDD_Fields_ID );
		}

		$posts = array(
			0 => sprintf( __( 'Current %s', EDD_Fields_ID ), $singular ),
		);

		foreach ( $post_types as $post_type ) {

			$args['post_type'] = $post_type;
			$query = new WP_Query( $args );

			if ( $query->have_posts() ) : 

				$grouped_posts = array();
				while ( $query->have_posts() ) : $query->the_post();

					if ( count( $post_types ) > 1 ) {
						// Store later for a <optgroup>
						$grouped_posts[ get_the_ID() ] = get_the_title();
					}
					else {
						$posts[ get_the_ID() ] = get_the_title();
					}

				endwhile;

				wp_reset_postdata();

				if ( count( $post_types ) > 1 ) {

					if ( $post_type == 'download' ) {
						$plural = edd_get_label_plural();
					}
					else {
						$post_type_object = get_post_type_object( $post_type );
						$plural = $post_type_object->labels->name;
					}

					// Create <optgroup>
					$posts[ $plural ] = $grouped_posts;

				}

			endif;

		}
		
		?>

		<div class="edd-fields-widget-form">

			<p>

				<label for="<?php echo $this->get_field_id( 'post_id' ); ?>">
					<?php echo sprintf( _x( 'Show Data for which %s:', 'Show for which Item Label', EDD_Fields_ID ), $singular ); ?>
				</label>

				<select id="<?php echo $this->get_field_id( 'post_id' ); ?>" class="widefat edd-fields-widget-post-id" name="<?php echo $this->get_field_name( 'post_id' ); ?>">

					<?php foreach ( $posts as $key => $value ) :

						if ( is_array( $value ) ) : ?>

							<optgroup label="<?php echo $key; ?>">

								<?php foreach ( $value as $post_id => $post_title ) : ?>

									<option value="<?php echo $post_id; ?>"<?php echo ( $post_id == $saved_post_id ) ? ' selected' : ''; ?>>
										<?php echo $post_title; ?>
									</option>

								<?php endforeach; ?>

							</optgroup>

						<?php else : ?>

							<option value="<?php echo $key; ?>"<?php echo ( $key == $saved_post_id ) ? ' selected' : ''; ?>>
								<?php echo $value; ?>
							</option>

						<?php endif;

					endforeach; ?>

				</select>

			</p>

			<p>

				<label for="<?php echo $this->get_field_id( 'shortcode' ); ?>">
					<?php echo _x( 'How to display the Data:', 'How to display the Data Label', EDD_Fields_ID ); ?>
				</label>
				<br />
				<label>
					<input type="radio" class="edd-fields-widget-shortcode" name="<?php echo $this->get_field_name( 'shortcode' ); ?>" value="table"<?php echo ( $saved_shortcode == 'table' ) ? ' checked' : ''; ?> /> <?php echo _x( 'Full Table', 'Widget Full Table Display', EDD_Fields_ID ); ?>
				</label>
				<br />
				<label>
					<input type="radio" class="edd-fields-widget-shortcode" name="<?php echo $this->get_field_name( 'shortcode' ); ?>" value="individual"<?php echo ( $saved_shortcode == 'individual' ) ? ' checked' : ''; ?> /> <?php echo _x( 'Single Value', 'Widget Single Value Display', EDD_Fields_ID ); ?>
				</label>

			</p>
			
			<div class="edd-fields-individual-options<?php echo ( $saved_shortcode !== 'individual' ) ? ' hidden' : ''; ?>">

				<p>
					
					<label for="<?php echo $this->get_field_id( 'field' ); ?>">
						<?php echo _x( 'Show which Field?', 'Show which Field Label', EDD_Fields_ID ); ?>
					</label>

					<select name="<?php echo $this->get_field_name( 'field' ); ?>" class="widefat edd-fields-widget-field" data-selected="<?php echo $saved_field; ?>">
						<option value="0">
							<?php echo _x( 'Select a Field', 'Field Select Default Option', EDD_Fields_ID ); ?>
						</option>
					</select>

				</p>
				
			</div>

		</div>
		
		<?php 
		
		// Enqueues our script automagically on both the Widgets page and the Customizer
		wp_enqueue_script( EDD_Fields_ID . '-admin' );
		
	}
	
	/**
	 * Sanitize widget form values as they are saved
	 * 
	 * @see WP_Widget::update()
	 * 
	 * @param		array $new_instance Values just sent to be saved
	 * @param		array $old_instance Previously saved values from database
	 *              
	 * @access		public
	 * @since		1.0.0
	 * @return		array Updated safe values to be saved
	 */
	public function update( $new_instance, $old_instance ) {
		
		$instance = array();
		$instance['post_id'] = ( ! empty( $new_instance['post_id'] ) ) ? strip_tags( $new_instance['post_id'] ) : '';
		$instance['shortcode'] = ( ! empty( $new_instance['shortcode'] ) ) ? strip_tags( $new_instance['shortcode'] ) : '';
		$instance['field'] = ( ! empty( $new_instance['field'] ) ) ? strip_tags( $new_instance['field'] ) : '';
		
		return $instance;
		
	}
	
	public static function get_fields_ajax_callback() {
		
		$fields = array();
		
		if ( (int) $_POST['post_id'] !== 0 ) {
		
			$saved_fields = get_post_meta( $_POST['post_id'], 'edd_fields', true );
			$selected_template = get_post_meta( $_POST['post_id'], 'edd_fields_template', true );

			if ( ! $selected_template ) $selected_template = 'custom';

			foreach ( $saved_fields[ $selected_template ] as $field ) {
				$fields[ edd_fields_sanitize_key( $field['key'] ) ] = $field['key'];
			}
			
		}
		else {
			
			$templates = edd_fields_get_templates();
			
			foreach ( $templates as $template ) {
				
				foreach ( $template['edd_fields_template_fields'] as $field ) {
					$fields[ edd_fields_sanitize_key( $field['label'] ) ] = $field['label'];
				}
				
			}
			
		}
		
		asort( $fields );
		
		$fields = array( 0 => _x( 'Select a Field', 'Field Select Default Option', EDD_Fields_ID ) ) + $fields;
		
		return wp_send_json_success( $fields );
		
	}
	
	/**
	 * WP Core has a weird bug with rendering Radio buttons in Customizer Widgets in Chrome only
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function customizer_styles() {
		
		wp_enqueue_style( EDD_Fields_ID . '-admin' );
		
	}
	
}

// AJAX Callback to get the Fields for our Select Field
add_action( 'wp_ajax_get_edd_fields_widget_field', array( 'EDD_Fields_Widget', 'get_fields_ajax_callback' ) );

register_widget( 'EDD_Fields_Widget' );