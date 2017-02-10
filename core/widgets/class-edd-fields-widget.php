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
		
		$post_types = apply_filters( 'edd_fields_metabox_post_types' , array( 'download' ) );
		
		$args = array(
			'numberposts' => -1,
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

		<p>
			
			<label for="<?php echo $this->get_field_id( 'post_id' ); ?>">
				<?php echo sprintf( _x( 'Show Data for which %s', 'Show for which Item Label', EDD_Fields_ID ), $singular ); ?>
			</label>
		
			<select id="<?php echo $this->get_field_id( 'post_id' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'post_id' ); ?>">

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
		
		<?php 
		
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
		
		return $instance;
		
	}
	
}

register_widget( 'EDD_Fields_Widget' );