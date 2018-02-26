<?php
/**
 * Plugin uninstall handler.
 *
 * @package Traduttore
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

array_map( function ( $file_or_folder ) {
	is_dir( $file_or_folder ) ? rmdir( $file_or_folder ) : unlink( $file_or_folder );
}, glob( get_temp_dir() . 'traduttore-*' ) );
