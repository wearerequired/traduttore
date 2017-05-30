<?php
/**
 * Plugin Name: Traduttore
 * Plugin URI:  https://github.com/wearerequired/traduttore/
 * Description: Customizations for translate.required.com
 * Version:     1.0.0
 * Author:      required
 * Author URI:  https://required.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: traduttore
 * Domain Path: /languages
 *
 * Copyright (c) 2017 required (email: info@required.ch)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Traduttore
 */

namespace Required\Traduttore;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

if ( ! class_exists( __NAMESPACE__ . '\Plugin' ) ) {
	trigger_error( sprintf( '%s does not exist. Check Composer\'s autoloader.',  __NAMESPACE__ . '\Plugin' ), E_USER_WARNING );
	return;
}

define( __NAMESPACE__ . '\PLUGIN_FILE', __FILE__ );

/**
 * Initializes the plugin.
 *
 * @since 1.0.0
 */
function init() {
	$plugin = new Plugin();
	$plugin->init();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );
