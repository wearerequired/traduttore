<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Required\Traduttore
 */

$_tests_dir    = getenv( 'WP_TESTS_DIR' );
$_gp_tests_dir = getenv( 'GP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! $_gp_tests_dir ) {
	$_gp_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress/build/wp-content/plugins/glotpress/tests/phpunit';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
	exit( 1 );
}

if ( ! file_exists( $_gp_tests_dir . '/bootstrap.php' ) ) {
	die( "GlotPress test suite could not be found. Have you run bin/install-wp-tests.sh ?\n" );
}

putenv( "WP_TESTS_DIR=$_tests_dir" );

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	function() use ( $_gp_tests_dir ) {
		require_once $_gp_tests_dir . '/includes/loader.php';

		require dirname( __DIR__, 2 ) . '/traduttore.php';
	}
);

global $wp_tests_options;

// So GlotPress doesn't bail early, see https://github.com/GlotPress/GlotPress-WP/blob/43bb5383e114835b09fc47c727d06e6d3ca8114e/glotpress.php#L142-L152.
$wp_tests_options['permalink_structure'] = '/%postname%';

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

require_once $_gp_tests_dir . '/lib/testcase.php';
require_once $_gp_tests_dir . '/lib/testcase-route.php';

if ( ! defined( 'TRADUTTORE_BITBUCKET_SYNC_SECRET' ) ) {
	define( 'TRADUTTORE_BITBUCKET_SYNC_SECRET', 'traduttore-test' );
}

if ( ! defined( 'TRADUTTORE_GITHUB_SYNC_SECRET' ) ) {
	define( 'TRADUTTORE_GITHUB_SYNC_SECRET', 'traduttore-test' );
}

if ( ! defined( 'TRADUTTORE_GITLAB_SYNC_SECRET' ) ) {
	define( 'TRADUTTORE_GITLAB_SYNC_SECRET', 'traduttore-test' );
}

if ( ! defined( 'TRADUTTORE_WP_BIN' ) && getenv( 'WP_CLI_BIN_DIR' ) ) {
	define( 'TRADUTTORE_WP_BIN', getenv( 'WP_CLI_BIN_DIR' ) . '/wp' );
}
