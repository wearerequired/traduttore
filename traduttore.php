<?php
/**
 * Plugin Name: Traduttore
 * Plugin URI:  https://github.com/wearerequired/traduttore/
 * Description: Add WordPress.org-style language pack API to your GlotPress installation for your WordPress projects hosted on GitHub.
 * Version:     3.0.0-alpha
 * Author:      required
 * Author URI:  https://required.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: traduttore
 * Domain Path: /languages
 *
 * Copyright (c) 2017-2018 required (email: info@required.ch)
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
 * @package Required\Traduttore
 */

namespace Required\Traduttore;

use WP_CLI;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

if ( ! class_exists( __NAMESPACE__ . '\Plugin' ) ) {
	trigger_error( sprintf( '%s does not exist. Check Composer\'s autoloader.', __NAMESPACE__ . '\Plugin' ), E_USER_WARNING );
	return;
}

define( __NAMESPACE__ . '\VERSION', '3.0.0-alpha' );
define( __NAMESPACE__ . '\PLUGIN_FILE', __FILE__ );

register_deactivation_hook( __FILE__, [ Plugin::class, 'on_plugin_deactivation' ] );

/**
 * Initializes the plugin.
 *
 * @since 1.0.0
 */
function init() {
	$plugin = new Plugin();
	$plugin->init();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\init', 1 );

if ( class_exists( '\WP_CLI' ) ) {
	WP_CLI::add_command( 'traduttore info', CLI\InfoCommand::class );
	WP_CLI::add_command( 'traduttore build', CLI\BuildCommand::class );
	WP_CLI::add_command( 'traduttore cache', CLI\CacheCommand::class );
	WP_CLI::add_command( 'traduttore update', CLI\UpdateCommand::class );
}
