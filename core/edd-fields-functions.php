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
 * @param       integer $post_id    Post ID
 * @param       string  $key        Key 
 *                                      
 * since        1.0.0
 * @return      string Value
 */
function edd_fields_get( $post_id, $name ) {
    
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
        
        // We need to grab values this way to ensure Nested Repeaters work
        $edd_option = $edd_options[ $args['id'] ];

        $args = wp_parse_args( $args, array(
            'id' => '',
            'std' => '',
            'classes' => array(),
            'desc' => false,
            'fields' => array(),
            'add_item_text' => __( 'Add Row', EDD_Fields::$plugin_id ),
            'delete_item_text' => __( 'Delete Row', EDD_Fields::$plugin_id ),
        ) );
        
        ?>

        <div class="edd_meta_table_wrap">

            <table class="widefat edd_repeatable_table<?php echo ( isset( $args['classes'] ) ) ? ' ' . implode( ' ', $args['classes'] ) : ''; ?>" width="100%" cellpadding="0" cellspacing="0">

                <thead>
                    <tr>
                        <th scope="col" class="edd-fields-field-handle"></th>
                        
                        <?php foreach ( $args['fields'] as $field_id => $field ) : ?>
                            <th scope="col"><?php echo $field['label']; ?></th>
                        <?php endforeach; ?>
                        
                        <th scope="col"></th>
                        
                    </tr>
                </thead>
                
                <?php if ( ! empty( $edd_option ) ) : 
                
                    $index = 0;
                    foreach ( $edd_option as $value ) : echo '<pre>'; var_dump( $edd_option ); echo '</pre><br /><br />'; ?>
                
                        <tr class="edd_variable_prices_wrapper edd_repeatable_row" data-key="<?php echo esc_attr( $index ); ?>">

                            <td>
                                <span class="edd_draghandle"></span>
                                <input type="hidden" name="<?php echo "{$args['id']}[$index][index]"; ?>" class="edd_repeatable_index" value="<?php echo $index; ?>"/>
                            </td>

                        <?php foreach ( $args['fields'] as $field_id => $field ) : 

                            if ( is_callable( "edd_{$field['type']}_callback" ) ) : ?>

                                <td>

                                    <?php
                                        // EDD Generates the Name Attr based on ID, so this nasty workaround is necessary
                                        $field['id'] = $args['id'] . '][' . $index . '][' . $field_id;
                                        $field['std'] = $value[ $field_id ];
        
                                        if ( $field['type'] == 'repeater' ) $field['classes'][] = 'nested-repeater';
        
                                        call_user_func( "edd_{$field['type']}_callback", $field ); 
                                    ?>

                                </td>

                            <?php endif;

                        endforeach; ?>
                            
                            <td>
                                <button class="edd_remove_repeatable" data-type="file" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">
                                    <span class="screen-reader-text"><?php echo $args['delete_item_text']; ?></span><span aria-hidden="true">&times;</span>
                                </button>
                            </td>

                        </tr>
                
                    <?php 
        
                    $index++;
        
                    endforeach;
        
                else : // This case only hits if no changes have ever been made. Even erasing Settings will leave a non-empty array behind ?>
        
                    <tr class="edd_variable_prices_wrapper edd_repeatable_row" data-key="0">

                        <td>
                            <span class="edd_draghandle"></span>
                            <input type="hidden" name="<?php echo "{$args['id']}[0][index]"; ?>" class="edd_repeatable_index" value="0"/>
                        </td>

                    <?php foreach ( $args['fields'] as $field_id => $field ) : 

                        if ( is_callable( "edd_{$field['type']}_callback" ) ) : ?>

                            <td>

                                <?php
                                    // EDD Generates the Name Attr based on ID, so this nasty workaround is necessary
                                    $field['id'] = $args['id'] . '][0][' . $field_id;
        
                                    if ( $field['type'] == 'repeater' ) $field['classes'][] = 'nested-repeater';
        
                                    call_user_func( "edd_{$field['type']}_callback", $field ); 
                                ?>

                            </td>

                        <?php endif;

                    endforeach; ?>
                        
                        <td>
                            <button class="edd_remove_repeatable" data-type="file" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;">
                                <span class="screen-reader-text"><?php echo $args['delete_item_text']; ?></span><span aria-hidden="true">&times;</span>
                            </button>
                        </td>

                    </tr>
        
                <?php endif; ?>
            
                <tr>
                    <td class="submit" colspan="4" style="float: none; clear:both; background:#fff;">
                        <button class="button-secondary edd_add_repeatable" style="margin: 6px 0;"><?php echo $args['add_item_text']; ?></button>
                    </td>
                </tr>

            </table>

        </div>

        
        <?php
        
    }
    
}