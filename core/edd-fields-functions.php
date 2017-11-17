<?php
/**
 * Provides helper functions.
 *
 * @since      1.0.0
 *
 * @package    EDD_Fields
 * @subpackage EDD_Fields/core
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Returns the main plugin object
 *
 * @since 1.0.0
 *
 * @return EDD_Fields
 */
function EDDFIELDS() {
	return EDD_Fields::instance();
}

/**
 * Collapse into a one-dimensional array of the Keys to find our Index
 *
 * @param array $fields
 * @param bool $sanitize
 *
 * @return array
 */
function edd_fields_get_key_list( $fields, $sanitize = false ) {

	$key_list = array_map( function ( $array ) use ( $sanitize ) {
		return $sanitize ? edd_fields_sanitize_key( $array['key'] ) : $array['key'];
	}, $fields );

	return $key_list;
}

/**
 * Function to grab an individual EDD Fields value. Useful for Theme Template Files.
 *
 * @param      string $key Key
 * @param      integer $post_id ID, defaults to current Post ID
 * @param      string $template Template, defaults to saved Template
 *
 * @since      1.0.0
 * @return      string Value
 */
function edd_fields_get( $name, $post_id = null, $template = null ) {

	if ( $post_id === null ) {
		$post_id = get_the_ID();
	}

	if ( $template === null ) {
		$template = get_post_meta( $post_id, 'edd_fields_template', true );
	}

	$fields = get_post_meta( $post_id, 'edd_fields', true );

	if ( ! $fields || ! isset( $fields[ $template ] ) ) {
		return false;
	}

	$key_list = edd_fields_get_key_list( $fields[ $template ], true );

	return $fields[ $template ][ array_search( edd_fields_sanitize_key( $name ), $key_list ) ]['value'];

}

/**
 * Grabs either the saved Templates or Defaults as appropriate
 *
 * @access        public
 * @since        1.0.0
 * @return        array Field Group Templates
 */
function edd_fields_get_templates() {

	$templates = edd_get_option( 'edd_fields_template_settings', false );

	// -1 is assumed empty
	if ( $templates === - 1 ||
	   $templates === false ) {

		return array();
	}

	return $templates;
}

/**
 * Grabs a specific template.
 *
 * @access public
 * @since  1.0.0
 *
 * @param string $which The template to grab (by label).
 *
 * @return array|bool The template, or false if doesn't exist.
 */
function edd_fields_get_template( $which ) {

	$templates = edd_fields_get_templates();

	foreach ( $templates as $template ) {

		if ( edd_fields_sanitize_key( $template['label'] ) == $which ) {

			return $template;
		}
	}

	return false;
}

/**
 * Sanitize a String to have only Alphanumeric characters. No special characters, spaces, etc.
 *
 * @param        string $key Template/Field Key
 *
 * @since        1.0.0
 * @return        string Sanitized Key
 */
function edd_fields_sanitize_key( $key ) {

	// Matches non-words, including underscore.
	$key = preg_replace( '[\W|_]', '', strtolower( $key ) );

	return apply_filters( 'edd_fields_sanitize_key', $key );

}

/**
 * Gets the posts select options.
 *
 * @since 1.0.0
 *
 * @return array
 */
function edd_fields_get_posts_select_options() {

	$post_types = apply_filters( 'edd_fields_metabox_post_types', array( 'download' ) );

	$args = array(
		'posts_per_page' => - 1,
		'orderby'        => 'name',
		'order'          => 'ASC',
		'post_status'    => 'publish',
	);

	if ( count( $post_types ) == 1 &&
	     $post_types[0] == 'download'
	) { // EDD Allows Users to Filter this. Some other plugins do too, but we're targeting EDD
		$singular = edd_get_label_singular();
	} else {
		$singular = _x( 'Item', 'Current Item Replacement Text for Widget', 'edd-fields' );
	}


	$posts = array(
		0 => sprintf( __( 'Current %s', 'edd-fields' ), $singular ),
	);

	// This is only used for Fields with their Type set to "posts", but running it here ensures we only do this query once
	foreach ( $post_types as $post_type ) {

		$args['post_type'] = $post_type;
		$query             = new WP_Query( $args );

		if ( $query->have_posts() ) :

			$grouped_posts = array();
			while ( $query->have_posts() ) : $query->the_post();

				if ( count( $post_types ) > 1 ) {
					// Store later for a <optgroup>
					$grouped_posts[ esc_attr( get_the_title() ) ] = get_the_title();
				} else {
					$posts[ esc_attr( get_the_title() ) ] = get_the_title();
				}

			endwhile;

			wp_reset_postdata();

			if ( count( $post_types ) > 1 ) {

				if ( $post_type == 'download' ) {
					$plural = edd_get_label_plural();
				} else {
					$post_type_object = get_post_type_object( $post_type );
					$plural           = $post_type_object->labels->name;
				}

				// Create <optgroup>
				$posts[ $plural ] = $grouped_posts;

			}

		endif;

	}

	return $posts;
}

function edd_fields_output_field_input( $field, $template, $saved = array(), $i ) {

	static $posts = null;

	if ( $posts === null ) {

		$posts = edd_fields_get_posts_select_options();
	}

	switch ( $field['type'] ) {

		case 'select':

			// We need to make our saved format less crazy
			$options = array();
			foreach ( $field['edd_fields_options'] as $option ) {

				$options[ esc_attr( $option['value'] ) ] = $option['value'];

			}

			echo EDD()->html->select( array(
				'name'             => "edd_fields[" . edd_fields_sanitize_key( $template['label'] ) . "][$i][value]",
				'selected'         => ( isset( $saved[ edd_fields_sanitize_key( $template['label'] ) ][ $i ]['value'] ) ) ? $saved[ edd_fields_sanitize_key( $template['label'] ) ][ $i ]['value'] : '',
				'options'          => $options,
				'show_option_all'  => false,
				'show_option_none' => false,
				'class'            => 'regular-text',
			) );

			break;

		case 'posts': ?>

            <select class="edd-select edd-select-chosen"
                    name="edd_fields[<?php echo edd_fields_sanitize_key( $template['label'] ); ?>][<?php echo $i; ?>][value]">

				<?php foreach ( $posts as $key => $value ) :

					if ( is_array( $value ) ) : ?>

                        <optgroup label="<?php echo $key; ?>">

							<?php foreach ( $value as $post_id => $post_title ) : ?>

                                <option value="<?php echo $post_id; ?>"<?php echo ( $post_id == $template[ edd_fields_sanitize_key( $template['label'] ) ][ $i ]['value'] ) ? ' selected' : ''; ?>>
									<?php echo $post_title; ?>
                                </option>

							<?php endforeach; ?>

                        </optgroup>

					<?php else : ?>

                        <option value="<?php echo $key; ?>"<?php echo ( $key == $template[ edd_fields_sanitize_key( $template['label'] ) ][ $i ]['value'] ) ? ' selected' : ''; ?>>
							<?php echo $value; ?>
                        </option>

					<?php endif;

				endforeach; ?>

            </select>

			<?php
			break;
		default:

			echo EDD()->html->text( array(
				'name'  => "edd_fields[" . edd_fields_sanitize_key( $template['label'] ) . "][$i][value]",
				'value' => ( isset( $saved[ edd_fields_sanitize_key( $template['label'] ) ][ $i ]['value'] ) ) ? $saved[ edd_fields_sanitize_key( $template['label'] ) ][ $i ]['value'] : '',
			) );

	}
}

function edd_fields_output_fields_input_table( $template, $saved = array(), $hidden = false ) {
	?>
    <div id="edd-fields-<?php echo edd_fields_sanitize_key( $template['label'] ); ?>"
         class="edd-fields-template<?php echo $hidden ? ' hidden' : ''; ?>">

        <table class="edd-fields-template-table widefat" width="100%" cellpadding="0" cellspacing="0">
            <thead>
            <tr>
                <th scope="col" class="edd-fields-name"><?php _e( 'Name', 'edd-fields' ); ?></th>
                <th scope="col" class="edd-fields-value"><?php _e( 'Value', 'edd-fields' ); ?></th>
            </tr>
            </thead>

            <tbody>
			<?php $i = 0; ?>
			<?php foreach ( $template['edd_fields_template_fields'] as $field ) : ?>
				<?php $name = 'edd_fields[' . edd_fields_sanitize_key( $field['label'] ) . "][$i]"; ?>
                <tr>
                    <th class="edd-fields-key">
						<?php echo esc_html( $field['label'] ); ?>
                        <input type="hidden" name="edd_fields[<?php echo edd_fields_sanitize_key( $template['label'] ); ?>][<?php echo $i; ?>][key]" value="<?php echo $field['label']; ?>" />
                    </th>

                    <td class="edd-fields-value">
						<?php edd_fields_output_field_input( $field, $template, $saved, $i ); ?>
                    </td>
                </tr>
				<?php $i ++; ?>
			<?php endforeach; ?>
            </tbody>
        </table>
    </div>
	<?php
}

/**
 * Returns the Default Templates if none are saved. This overrides any default values for the Fields
 * 
 * @since		1.0.0
 * @return		array Default Templates
 */
function get_edd_fields_default_templates() {

	$ebook = apply_filters( 'edd_fields_ebook_template_defaults', array(
		'label' => _x( 'Ebook', 'Ebook Template', 'edd-fields' ),
		'edd_fields_template_fields' => array(
			array(
				'label' => _x( 'Author', 'Ebook Template: Author', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'Number of Pages', 'Ebook Template: Number of Pages', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'Publisher', 'Ebook Template: Publisher', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'Format', 'Ebook Template: Format', 'edd-fields' ),
				'type' => 'text',
			),
		),
	) );

	$wp_plugin = apply_filters( 'edd_fields_wordpress_plugin_template_defaults', array(
		'label' => _x( 'WordPress Plugin', 'WordPress Plugin Template', 'edd-fields' ),
		'edd_fields_template_fields' => array(
			array(
				'label' => _x( 'Required WordPress Version', 'WordPress Plugin Template: Required WordPress Version', 'edd-fields' ),
				'type' => 'select',
				'edd_fields_options' => array(
					array(
						'value' => '4.4',
					),
					array(
						'value' => '4.5',
					),
					array(
						'value' => '4.6',
					),
					array(
						'value' => '4.7',
					),
				),
			),
			array(
				'label' => _x( 'Required PHP Version', 'WordPress Plugin Template: Required PHP Version', 'edd-fields' ),
				'type' => 'select',
				'edd_fields_options' => array(
					array(
						'value' => '5.6',
					),
					array(
						'value' => '7.0',
					),
					array(
						'value' => '7.1',
					),
				),
			),
			array(
				'label' => _x( 'Contributors', 'WordPress Plugin Template: Contributors', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'Supported Languages', 'WordPress Plugin Template: Supported Languages', 'edd-fields' ),
				'type' => 'text',
			),
		),
	) );

	$wp_theme = apply_filters( 'edd_fields_wordpress_theme_template_defaults', array(
		'label' => _x( 'WordPress Theme', 'WordPress Theme Template', 'edd-fields' ),
		'edd_fields_template_fields' => array(
			array(
				'label' => _x( 'Required WordPress Version', 'WordPress Theme Template: Required WordPress Version', 'edd-fields' ),
				'type' => 'select',
				'edd_fields_options' => array(
					array(
						'value' => '4.4',
					),
					array(
						'value' => '4.5',
					),
					array(
						'value' => '4.6',
					),
					array(
						'value' => '4.7',
					),
				),
			),
			array(
				'label' => _x( 'Required PHP Version', 'WordPress Theme Template: Required PHP Version', 'edd-fields' ),
				'type' => 'select',
				'edd_fields_options' => array(
					array(
						'value' => '5.6',
					),
					array(
						'value' => '7.0',
					),
					array(
						'value' => '7.1',
					),
				),
			),
			array(
				'label' => _x( 'Contributors', 'WordPress Theme Template: Contributors', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'Supported Languages', 'WordPress Theme Template: Supported Languages', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'Responsive', 'WordPress Theme Template: Responsive', 'edd-fields' ),
				'type' => 'select',
				'edd_fields_options' => array(
					array(
						'value' => __( 'Yes', 'edd-fields' ),
					),
					array(
						'value' => __( 'No', 'edd-fields' ),
					),
				),
			),
		),
	) );

	$music = apply_filters( 'edd_fields_music_template_defaults', array(
		'label' => _x( 'Music', 'Music Template', 'edd-fields' ),
		'edd_fields_template_fields' => array(
			array(
				'label' => _x( 'Artist', 'Music Template: Artist', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'Genre', 'Music Template: Genre', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'Length', 'Music Template: Length', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'Producer', 'Music Template: Producer', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'Explicit', 'Music Template: Explicit', 'edd-fields' ),
				'type' => 'select',
				'edd_fields_options' => array(
					array(
						'value' => __( 'Yes', 'edd-fields' ),
					),
					array(
						'value' => __( 'No', 'edd-fields' ),
					),
				),
			),
		),
	) );

	$software = apply_filters( 'edd_fields_software_template_defaults', array(
		'label' => _x( 'Software', 'Software Template', 'edd-fields' ),
		'edd_fields_template_fields' => array(
			array(
				'label' => _x( 'Operating System', 'Software Template: Operating System', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'File Type', 'Software Template: File Type', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'Disk Space', 'Software Template: Disk Space', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'Required Memory', 'Software Template: Required Memory', 'edd-fields' ),
				'type' => 'text',
			),
		),
	) );

	$photography = apply_filters( 'edd_fields_photography_template_defaults', array(
		'label' => _x( 'Photography', 'Photography Template', 'edd-fields' ),
		'edd_fields_template_fields' => array(
			array(
				'label' => _x( 'License', 'Photography Template: License', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'File Type', 'Photography Template: File Type', 'edd-fields' ),
				'type' => 'text',
			),
			array(
				'label' => _x( 'Dimensions', 'Photography Template: Dimensions', 'edd-fields' ),
				'type' => 'text',
			),
		),
	) );

	return apply_filters( 'edd_fields_template_defaults', array_merge( 
		array( $ebook ),
		array( $wp_plugin ),
		array( $wp_theme ),
		array( $music ),
		array( $software ),
		array( $photography )
	) );

}