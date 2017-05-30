<?php
/**
 * Template functions.
 */

namespace Required\Traduttore\Templates;

use const Required\Traduttore\PLUGIN_FILE;

// Enqueue custom stylesheet.
wp_register_style( 'required-styles', plugins_url( 'assets/css/styles.css', PLUGIN_FILE ), [ 'gp-base' ], '20170530' );
gp_enqueue_style( 'required-styles' );

// Change the home title to "Translate".
add_filter( 'gp_home_title', function() {
	return 'Translate';
} );

// Reduce items in the navigation.
add_filter( 'gp_nav_menu_items', function( $items, $location ) {
	if ( 'main' === $location ) {
		return [];
	} elseif ( 'side' === $location ) {
		$user = wp_get_current_user();
		unset( $items[ gp_url_profile( $user->user_nicename ) ] );
	}

	return $items;
}, 10, 2 );
