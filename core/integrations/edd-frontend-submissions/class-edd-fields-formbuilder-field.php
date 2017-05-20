<?php

class EDD_Fields_FormBuilderField extends FES_Field {
	/** @var string Version of field */
	public $version = '1.0.0';

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'is_meta'     => true,
		'forms'       => array(
			'submission' => true,
		),
		'position'    => 'extension',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_add_to_formbuilder'      => true,
			'can_change_meta_key'         => false,
		),
		'template'    => 'edd_fields',
		'title'       => 'EDD Fields',
		'phoenix'     => true,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two text fields. Stored in db. */
	public $characteristics = array(
		'name'            => 'edd_fields',
		'template'        => 'edd_fields',
		'public'          => true,
		'required'        => false,
		'label'           => 'Download Fields',
		'css'             => '',
		'help'            => '',
		'fields_template' => '',
	);

	public function get_label() {

		return __( 'Download Fields', 'edd-fields' );
	}

	public function set_title() {
		$title                   = _x( 'Download Fields', 'EDD Fields Field title translation', 'edd-fields' );
		$title                   = apply_filters( 'edd_fields_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;
	}

	/** Returns the HTML to render a field in frontend */
	public function render_field_frontend( $user_id = - 2, $readonly = - 2 ) {

	    if ( $user_id === - 2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === - 2 ) {
			$readonly = $this->readonly;
		}

		$user_id  = apply_filters( 'fes_render_select_field_user_id_frontend', $user_id, $this->id );
		$readonly = apply_filters( 'fes_render_select_field_readonly_frontend', $readonly, $user_id, $this->id );
		$value    = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );
		$required = $this->required( $readonly );

		$field_template = $this->characteristics['fields_template'];

		if ( $field_template ) {

			$template = edd_fields_get_template( $field_template );
		}

		$field_templates = edd_fields_get_templates();

		if ( $this->save_id > 0 ) {
			$selected = $this->get_meta( $this->save_id, $this->name(), $this->type );
		} else {
			$selected = isset( $this->characteristics['selected'] ) ? $this->characteristics['selected'] : '';
		}

		$data_type = 'select';
		$css       = '';
		$output    = '';
		$output .= sprintf( '<fieldset class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output .= $this->label( $readonly );
		ob_start(); ?>
        <div class="fes-fields">

			<?php if ( isset( $template ) ) : ?>

                <input type="hidden" name="edd_fields_template" id="edd_fields_template"
                       value="<?php echo esc_attr( $field_template ); ?>"/>

				<?php edd_fields_output_fields_input_table( $template ); ?>

			<?php else : ?>

                <p>
                    <label><?php _e( 'Field Template', 'edd-fields' ); ?></label>
                    <select id="edd_fields_template" name="edd_fields_template">
						<?php foreach ( $field_templates as $template ) : ?>
                            <option value="<?php echo esc_attr( edd_fields_sanitize_key( $template['label'] ) ); ?>">
								<?php echo esc_html( $template['label'] ); ?>
                            </option>
						<?php endforeach; ?>
                    </select>
                </p>

				<?php foreach ( $field_templates as $template ) : ?>
					<?php edd_fields_output_fields_input_table( $template, array(), true ); ?>
				<?php endforeach; ?>

			<?php endif; ?>
        </div>
		<?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';

		return $output;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = - 2, $insert = false ) {
		$removable   = $this->can_remove_from_formbuilder();
		$first_name  = sprintf( '%s[%d][first]', 'fes_input', $index );
		$help        = esc_attr( __( 'First element of the select dropdown. Leave this empty if you don\'t want to show this field', 'edd_fes' ) );

		$fields_template_name  = sprintf( '%s[%d][fields_template]', 'fes_input', $index );
		$fields_template_value = esc_attr( $this->characteristics['fields_template'] );
		$field_templates       = edd_fields_get_templates();

		ob_start(); ?>
        <li class="edd_fields">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>
			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
			<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name ); ?>
			<?php FES_Formbuilder_Templates::standard( $index, $this ); ?>

            <div class="fes-form-rows">
                <label><?php _e( 'Field Template', 'edd-fields' ); ?></label>
                <select name="<?php echo $fields_template_name; ?>" class="smallipopInput"
                        title="<?php _e( 'Available Download Fields for the Vendor', 'edd_fes' ); ?>">
                    <option value="">
						<?php _e( 'Allow Template Selection', 'edd-fields' ); ?>
                    </option>

					<?php foreach ( $field_templates as $template ) : ?>
                        <option value="<?php echo esc_attr( edd_fields_sanitize_key( $template['label'] ) ); ?>"
							<?php selected( $fields_template_value, edd_fields_sanitize_key( $template['label'] ) ); ?>>
							<?php echo esc_html( $template['label'] ); ?>
                        </option>
					<?php endforeach; ?>
                </select>
            </div>
        </li>

		<?php
		return ob_get_clean();
	}

	public function sanitize( $values = array(), $save_id = - 2, $user_id = - 2 ) {

		$name = $this->name();

		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}

	public function save_field_frontend( $save_id = - 2, $value = '', $user_id = - 2 ) {

	    // Also save template before saving the fields
		$template = isset( $_POST['edd_fields_template'] ) ? $_POST['edd_fields_template'] : '';

		if ( $template ) {

			if ( $save_id == - 2 ) {

				$save_id = $this->save_id;
			}

			update_post_meta( $save_id, 'edd_fields_template', $template );
		}

		parent::save_field_frontend( $save_id, $value, $user_id );
	}

	/**
	 * Disable this, duh.
	 *
	 * @param int $save_id
	 * @param string $value
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function save_field_admin( $save_id = - 2, $value = '', $user_id = - 2 ) {
		return false;
	}
}

// Add page script
add_action( 'current_screen', 'edd_fields_fes_formbuilder_page' );

function edd_fields_fes_formbuilder_page( $screen ) {

	if ( $screen->id != 'fes-forms' ) {

		return;
	}

	add_action( 'admin_enqueue_scripts', 'edd_fields_fes_formbuilder_page_scripts' );
}

function edd_fields_fes_formbuilder_page_scripts() {

	wp_enqueue_script( 'edd-fields-fes' );
}