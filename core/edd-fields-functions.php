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
            'sortable' => true,
            'collapsable' => false,
            'collapsable_title' => __( 'New Row', EDD_Fields::$plugin_id ),
            'nested' => false,
            'layout' => 'table',
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
        
        if ( $args['sortable'] ) {
            $args['classes'][] = 'edd-repeater-sortable';
        }
        
        if ( $args['collapsable'] ) {
            $args['classes'][] = 'edd-repeater-collapsable';
        }
        
        if ( $args['layout'] == 'table' ) {
            $args['classes'][] = 'edd-repeater-layout-table';
        }
        else {
            $args['classes'][] = 'edd-repeater-layout-row';
        }
        
        ?>

        <?php if ( $args['nested'] ) : ?>

            <label for="<?php echo $args['id']; ?>"><?php echo $args['desc']; ?></label>

        <?php endif; ?>

        <div<?php echo ( ! $args['nested'] ) ? ' data-edd-repeater' : ''; ?><?php echo ( $args['sortable'] ) ? ' data-repeater-sortable' : ''; ?><?php echo ( $args['collapsable'] ) ? ' data-repeater-collapsable' : ''; ?> class="edd-repeater edd_meta_table_wrap<?php echo ( isset( $args['classes'] ) ) ? ' ' . implode( ' ', $args['classes'] ) : ''; ?>">
            
            <div data-repeater-list="<?php echo ( ! $args['nested'] ) ? 'edd_settings[' . $args['id'] . ']' : $args['id']; ?>" class="edd-repeater-list">

                    <?php for ( $index = 0; $index < $field_count; $index++ ) : $value = $edd_option[$index]; ?>
                
                        <div data-repeater-item<?php echo ( ! isset( $edd_option[$index] ) && ! $args['nested'] ) ? ' data-repeater-dummy style="display: none;"' : ''; ?> class="edd-repeater-item<?php echo ( $args['collapsable'] ) ? ' closed' : ''; ?>">
                            
                            <?php if ( ! $args['nested'] ) : ?>
                                <table class="repeater-header widefat" width="100%" cellpadding="0" cellspacing="0"<?php echo ( $args['collapsable'] ) ? ' data-repeater-collapsable-handle' : '';?>>
                                    
                                    <tbody>
                                        
                                        <tr>

                                            <?php if ( $args['sortable'] ) : ?>
                                                <td class="edd-repeater-field-handle">
                                                    <span class="edd_draghandle" data-repeater-item-handle></span>
                                                </td>
                                            <?php endif; ?>

                                            <td>
                                                <h2 data-repeater-collapsable-default="<?php echo $args['collapsable_title']; ?>">
                                                    <span class="title">

                                                        <?php if ( isset( $edd_option[$index] ) && reset( $edd_option[$index] ) !== '' ) : 

                                                            // Surprisingly, this is the most efficient way to do this. http://stackoverflow.com/a/21219594
                                                            foreach ( $value as $key => $setting ) : ?>
                                                                <?php echo $setting; ?>
                                                            <?php 
                                                                break;
                                                            endforeach; 

                                                        else: ?>

                                                            <?php echo $args['collapsable_title']; ?>

                                                        <?php endif; ?>

                                                    </span>

                                                    <?php if ( $args['collapsable'] ) : ?>
                                                        <span class="edd-repeater-collapsable-handle-arrow">
                                                            <span class="opened dashicons dashicons-arrow-up"></span>
                                                            <span class="closed dashicons dashicons-arrow-down"></span>
                                                        </span>
                                                    <?php endif; ?>

                                                </h2>
                                            </td>

                                            <td class="edd-repeater-controls">
                                                <input data-repeater-delete type="button" class="button" value="<?php echo $args['delete_item_text']; ?>" />
                                            </td>
                            
                                        </tr>
                                        
                                    </tbody>

                                </table>
                            <?php endif; ?>
                            
                            <div class="edd-repeater-content">

                                <table class="widefat" width="100%" cellpadding="0" cellspacing="0">

                                    <tbody>

                                        <tr>
                                            
                                            <?php if ( $args['nested'] && $args['sortable'] ) : ?>

                                                <td class="edd-repeater-field-handle">
                                                    <span class="edd_draghandle" data-repeater-item-handle></span>
                                                </td>

                                            <?php endif; ?>

                                            <?php foreach ( $args['fields'] as $field_id => $field ) : 

                                                if ( is_callable( "edd_{$field['type']}_callback" ) ) : 
        
                                                    // EDD Generates the Name Attr based on ID, so this nasty workaround is necessary
                                                    $field['id'] = $field_id;
                                                    $field['std'] = ( isset( $value[ $field_id ] ) ) ? $value[ $field_id ] : $field['std'];
                                            
                                                    if ( $field['type'] !== 'hook' ) : ?>

                                                        <td<?php echo ( $field['type'] == 'repeater' ) ? ' class="repeater-container"' : ''; ?>>

                                                            <?php
                                                                if ( $field['type'] == 'repeater' ) {
                                                                    $field['nested'] = true;
                                                                    $field['classes'][] = 'nested-repeater';
                                                                }

                                                                call_user_func( "edd_{$field['type']}_callback", $field ); 
                                                            ?>

                                                        </td>
                                            
                                                    <?php else : 
        
                                                        call_user_func( "edd_{$field['type']}_callback", $field ); 
        
                                                    endif;

                                                endif;

                                            endforeach;

                                            if ( $args['nested'] ) : ?>

                                                <td>
                                                    <span class="screen-reader-text"><?php echo $args['delete_item_text']; ?></span>
                                                    <input data-repeater-delete type="button" class="edd_remove_repeatable" data-type="file" style="background: url(<?php echo admin_url('/images/xit.gif'); ?>) no-repeat;" />
                                                </td>

                                            <?php endif; ?>

                                            </tr>

                                    </tbody>

                                </table>
                                
                            </div>
                            
                        </div>

                    <?php endfor; ?>       

            </div>
            
            <input data-repeater-create type="button" class="button" style="margin-top: 6px;" value="<?php echo $args['add_item_text']; ?>" />

        </div>
        
        <?php
        
    }
    
}