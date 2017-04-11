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
 * Collapse into a one-dimensional array of the Keys to find our Index
 *
 * @param array $fields
 * @param bool $sanitize
 *
 * @return array
 */
function edd_fields_get_key_list( $fields, $sanitize = false ) {

	$key_list = array_map( function( $array ) use ( $sanitize ) {
		return $sanitize ? edd_fields_sanitize_key( $array['key'] ) : $array['key'];
	}, $fields );

	return $key_list;
}

/**
 * Function to grab an individual EDD Fields value. Useful for Theme Template Files.
 * 
 * @param	  string  $key		Key
 * @param	  integer $post_id	ID, defaults to current Post ID
 * @param	  string  $template Template, defaults to saved Template
 *									                
 * @since	  1.0.0
 * @return	  string Value
 */
function edd_fields_get( $name, $post_id = null, $template = null ) {
	
	if ( $post_id === null ) $post_id = get_the_ID();
	
	if ( $template === null ) {
		$template = get_post_meta( $post_id, 'edd_fields_template', true );
	}
	
	$fields = get_post_meta( $post_id, 'edd_fields', true );
	
	if ( ! $fields || ! isset( $fields[ $template ] ) ) return false;

	$key_list = edd_fields_get_key_list( $fields[ $template ], true );

	return $fields[ $template ][ array_search( edd_fields_sanitize_key( $name ), $key_list ) ]['value'];
	
}
	
/**
 * Grabs either the saved Templates or Defaults as appropriate
 * 
 * @access		public
 * @since		1.0.0
 * @return		array Field Group Templates
 */
function edd_fields_get_templates() {

	$templates = edd_get_option( 'edd_fields_template_settings', false );

	// -1 is assumed empty
	if ( $templates === -1 ) {

		return array();
	}

	return $templates;
}
	
/**
 * Sanitize a String to have only Alphanumeric characters. No special characters, spaces, etc.
 * 
 * @param		string $key Template/Field Key
 *                    
 * @since		1.0.0
 * @return		string Sanitized Key
 */
function edd_fields_sanitize_key( $key ) {

	// Matches non-words, including underscore.
	$key = preg_replace( '[\W|_]', '', strtolower( $key ) );

	return apply_filters( 'edd_fields_sanitize_key', $key );

}