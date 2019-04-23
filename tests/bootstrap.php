<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Translation Stats
 */

$tstats_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $tstats_tests_dir ) {
	$tstats_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $tstats_tests_dir . '/includes/functions.php' ) ) {
	echo 'Could not find ' . esc_html( $tstats_tests_dir ) . '/includes/functions.php, have you run bin/install-wp-tests.sh ?';
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $tstats_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function tstats_manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/translation-stats.php';
}
tests_add_filter( 'muplugins_loaded', 'tstats_manually_load_plugin' );

// Start up the WP testing environment.
require $tstats_tests_dir . '/includes/bootstrap.php';
