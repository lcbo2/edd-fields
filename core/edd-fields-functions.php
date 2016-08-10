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
 * Function to grab an individual EDD Fields value. Useful for Theme Template Files.
 * 
 * @param       string  $key        Key 
 * @param       integer $post_id    Post ID
 *                                      
 * since        1.0.0
 * @return      string Value
 */
function edd_fields_get( $name, $post_id ) {
    
    if ( $post_id === null ) $post_id = get_the_ID();
    
    $edd_fields = get_post_meta( $post_id, 'edd_fields', true );

    // Collapse into a one-dimensional array of the Keys to find our Index
    $key_list = array_map( function( $array ) {
        return $array['key'];
    }, $edd_fields );

    return $edd_fields[ array_search( $name, $key_list ) ]['value'];
    
}

if ( ! function_exists( 'edd_repeater_callback' ) ) {
    
    function edd_repeater_callback( $args ) {
        
        global $edd_options;

        $args = wp_parse_args( $args, array(
            'id' => '',
            'std' => '',
            'classes' => array(),
            'desc' => false,
            'fields' => array(),
            'add_item_text' => __( 'Add Row', EDD_Fields::$plugin_id ),
            'delete_item_text' => __( 'Delete Row', EDD_Fields::$plugin_id ),
        ) );
        
        // We need to grab values this way to ensure Nested Repeaters work
        if ( $args['std'] == '' ) {
            $edd_option = $edd_options[ $args['id'] ];
        }
        else {
            $edd_option = $args['std'];
        }
        
        ?>

        <div<?php echo ( ! in_array( 'nested-repeater', $args['classes'] ) ) ? ' data-edd-repeater' : ''; ?> class="edd-repeater edd_meta_table_wrap<?php echo ( isset( $args['classes'] ) ) ? ' ' . implode( ' ', $args['classes'] ) : ''; ?>">
            
            <table class="widefat" width="100%" cellpadding="0" cellspacing="0">

                <thead>
                    <tr>
                        <th scope="col" class="edd-fields-field-handle"></th>
                        
                        <?php foreach ( $args['fields'] as $field_id => $field ) : ?>
                            <th scope="col"><?php echo $field['label']; ?></th>
                        <?php endforeach; ?>
                        
                        <th scope="col"></th>
                        
                    </tr>
                </thead>
                
                <tbody data-repeater-list="<?php echo ( ! in_array( 'nested-repeater', $args['classes'] ) ) ? 'edd_settings[' . $args['id'] . ']' : $args['id']; ?>">

                    <?php if ( ! empty( $edd_option ) ) : 

                        $index = 0;
                        foreach ( $edd_option as $value ) : ?>

                            <tr class="edd_variable_prices_wrapper" data-repeater-item data-key="<?php echo esc_attr( $index ); ?>">

                                <td>
                                    <span class="edd_draghandle" data-repeater-item-handle></span>
                                    <input type="hidden" name="<?php echo "{$args['id']}[$index][index]"; ?>" class="edd_repeatable_index" value="<?php echo $index; ?>"/>
                                </td>

                            <?php foreach ( $args['fields'] as $field_id => $field ) : 

                                if ( is_callable( "edd_{$field['type']}_callback" ) ) : ?>

                                    <td>

                                        <?php
                                            // EDD Generates the Name Attr based on ID, so this nasty workaround is necessary
                                            $field['id'] = $field_id;
                                            $field['std'] = $value[ $field_id ];

                                            if ( $field['type'] == 'repeater' ) $field['classes'][] = 'nested-repeater';

                                            call_user_func( "edd_{$field['type']}_callback", $field ); 
                                        ?>

                                    </td>

                                <?php endif;

                            endforeach; ?>

                                <td>
                                    <span class="screen-reader-text"><?php echo $args['delete_item_text']; ?></span>
                                    <input data-repeater-delete type="button" class="edd_remove_repeatable" data-type="file" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;" />
                                </td>

                            </tr>

                        <?php 

                        $index++;

                        endforeach;

                    else : // This case only hits if no changes have ever been made. Even erasing Settings will leave a non-empty array behind ?>

                        <tr class="edd_variable_prices_wrapper" data-repeater-item data-key="0">

                            <td>
                                <span class="edd_draghandle"></span>
                                <input type="hidden" name="<?php echo "{$args['id']}[0][index]"; ?>" class="edd_repeatable_index" value="0"/>
                            </td>

                        <?php foreach ( $args['fields'] as $field_id => $field ) : 

                            if ( is_callable( "edd_{$field['type']}_callback" ) ) : ?>

                                <td>

                                    <?php
                                        // jQuery Repeater deals with this for us based on data-repeater-list
                                        $field['id'] = $field_id;
                                        $field['std'] = $value[ $field_id ];

                                        if ( $field['type'] == 'repeater' ) $field['classes'][] = 'nested-repeater';

                                        call_user_func( "edd_{$field['type']}_callback", $field ); 
                                    ?>

                                </td>

                            <?php endif;

                        endforeach; ?>

                            <td>
                                <span class="screen-reader-text"><?php echo $args['delete_item_text']; ?></span>
                                <input data-repeater-delete type="button" class="edd_remove_repeatable" data-type="file" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;" />
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>
                
            </table>
            
            <input data-repeater-create type="button" class="button-secondary" style="float: none; clear:both; background:#fff; margin: 6px;" value="<?php echo $args['add_item_text']; ?>" />

        </div>

        
        <?php
        
    }
    
}