<?php
/**
 * Provides helper functions.
 *
 * @since	  1.0.0
 *
 * @package	EDD_Fields
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
 * @param	  string  $key		Key
 * @param	  integer $post_id	ID, defaults to current Post ID
 * @param	  string  $template Template, defaults to saved Template
 *									                
 * since	  1.0.0
 * @return	  string Value
 */
function edd_fields_get( $name, $post_id = null, $template = null ) {
	
	if ( $post_id === null ) $post_id = get_the_ID();
	
	if ( $template === null ) {
		$template = get_post_meta( $post_id, 'edd_fields_tab', true );
	}
	
	$fields = get_post_meta( $post_id, 'edd_fields', true );
	
	if ( ! $fields || ! isset( $fields[ $template ] ) ) return false;

	// Collapse into a one-dimensional array of the Keys to find our Index
	$key_list = array_map( function( $array ) {
		return EDDFIELDS()->utility->sanitize_key( $array['key'] );
	}, $fields[ $template ] );

	return $fields[ $template ][ array_search( EDDFIELDS()->utility->sanitize_key( $name ), $key_list ) ]['value'];
	
}