<?php
/**
 * The Shortcodes for EDD Fields
 *
 * @since 1.0.0
 *
 * @package EDD_Fields
 * @subpackage EDD_Fields/core/front
 */

defined( 'ABSPATH' ) || die();

class EDD_Fields_Shortcodes {

	/**
	 * EDD_Fields_Shortcodes constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {

		// Output on Frontend
		add_shortcode( 'edd_fields_table', array( $this, 'edd_fields_table_shortcode' ) );

		// Grab inividual value via Shortcode
		add_shortcode( 'edd_field', array( $this, 'edd_field_shortcode' ) );

		// Force our Shortcode on Download Singles
		// Priority of 9 puts it above the purchase button
		add_filter( 'the_content', array( $this, 'inject_shortcode' ), 9 );

	}

	/**
	 * Outputs Download Fields as a table via Shortcode
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      HTML
	 */
	public function edd_fields_table_shortcode( $atts, $content ) {

		$atts = shortcode_atts(
			array(
				'class'   => '',
				'post_id' => get_the_ID(),
			),
			$atts,
			'edd_fields_table'
		);

		ob_start();

		$template = edd_fields_get_chosen_template( $atts['post_id'] );

		$fields = edd_fields_get_all_saved_fields( $atts['post_id'] );

		$show = false;
		if ( $fields && isset( $fields[ $template ] ) && ! empty( $fields[ $template ] ) ) {

			// Erase out potentially empty fields
			$fields[ $template ][0] = array_filter( $fields[ $template ][0] );

			// Erase out potentially empty template
			$fields[ $template ] = array_filter( $fields[ $template ] );

			$show = ! empty( $fields[ $template ] );
		}

		if ( $show ) : ?>

            <table class="edd-fields<?php echo ( $atts['class'] !== '' ) ? ' ' . $atts['class'] : ''; ?>">

				<?php foreach ( $fields[ $template ] as $row ) : ?>

                    <tr>

                        <th>
							<?php echo $row['key']; ?>
                        </th>

                        <td>
							<?php echo $row['value']; ?>
                        </td>

                    </tr>

				<?php endforeach; ?>

            </table>

		<?php endif;

		$output = ob_get_contents();
		ob_get_clean();

		return $output;

	}

	/**
	 * Shortcode to grab individual EDD Fields Values
	 *
	 * @param       array $atts Shortcode Attributes
	 * @param       string $content We're not actually using this, but I like to have it there for completeness
	 *
	 * @access      public
	 * @since       1.0.0
	 * @return      string
	 */
	public function edd_field_shortcode( $atts, $content ) {

		$atts = shortcode_atts(
			array(
				'name'    => '',
				'post_id' => get_the_ID(),
			),
			$atts,
			'edd_field'
		);

		if ( $atts['name'] == '' ) {
			return __( 'You must specify a Field Name. Example: [edd_field name="test"]', 'edd-fields' );
		}

		return edd_fields_get( $atts['name'], $atts['post_id'] );

	}

	/**
	 * Force our Shortcode to load on Single Downloads
	 *
	 * @param        string $content The Content
	 *
	 * @access        public
	 * @since        1.0.0
	 * @return        string The Content
	 */
	public function inject_shortcode( $content ) {

		$post_types = apply_filters( 'edd_fields_metabox_post_types', array( 'download' ) );

		if ( is_single() && in_array( get_post_type(), $post_types ) ) {

			$inject_shortcode = EDDFIELDS()->utility->is_shortcode_injected();

			if ( $inject_shortcode ) {
				$content .= '[edd_fields_table]';
			}

		}

		return $content;

	}

}