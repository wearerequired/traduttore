<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Required\Traduttore
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
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

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

tests_add_filter( 'muplugins_loaded', function() use ( $_gp_tests_dir ) {
	require_once $_gp_tests_dir . '/includes/loader.php';

	require dirname( dirname( __DIR__ ) ) . '/traduttore.php';
} );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

require_once $_gp_tests_dir . '/lib/testcase.php';
