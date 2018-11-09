<?php
/**
 * Plugin uninstall handler.
 *
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use WP_Filesystem_Base;

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/* @var WP_Filesystem_Base $wp_filesystem */
global $wp_filesystem;

if ( ! $wp_filesystem ) {
	require_once ABSPATH . '/wp-admin/includes/admin.php';

	\WP_Filesystem();
}

if ( $wp_filesystem ) {
	array_map(
		function ( $file_or_folder ) use ( $wp_filesystem ) {
			$wp_filesystem->delete( $file_or_folder, true, is_dir( $file_or_folder ) ? 'd' : 'f' );
		},
		glob( get_temp_dir() . 'traduttore-*' )
	);

	$wp_filesystem->rmdir( ZipProvider::get_cache_dir(), true );
}

/* @var \wpdb $wpdb */
global $wpdb;

$meta_key_prefix = '_traduttore_';

$query = $wpdb->prepare( "DELETE FROM `$wpdb->gp_meta` WHERE `meta_key` LIKE %s ", $wpdb->esc_like( $meta_key_prefix ) . '%' );

// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$wpdb->query( $query );
