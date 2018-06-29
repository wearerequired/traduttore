<?php
/**
 * Plugin uninstall handler.
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/* @var WP_Filesystem_Base $wp_filesystem */
global $wp_filesystem;

if ( ! $wp_filesystem ) {
	require_once ABSPATH . '/wp-admin/includes/admin.php';

	if ( ! \WP_Filesystem() ) {
		return false;
	}
}

$wp_filesystem->rmdir( $file_or_folder, true );

array_map(
	function ( $file_or_folder ) use ( $wp_filesystem ) {
		$wp_filesystem->delete( $file_or_folder, true, is_dir( $file_or_folder ) ? 'd' : 'f' );
	}, glob( get_temp_dir() . 'traduttore-*' )
);

$wp_filesystem->rmdir( ZipProvider::get_cache_dir(), true );
