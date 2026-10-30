<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Translation_Stats
 */

$translation_stats_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $translation_stats_tests_dir ) {
	$translation_stats_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$translation_stats_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false !== $translation_stats_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $translation_stats_phpunit_polyfills_path ); // phpcs:ignore
}

if ( ! file_exists( "{$translation_stats_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$translation_stats_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$translation_stats_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function translation_stats_manually_load_plugin() {
	require dirname( dirname( __DIR__ ) ) . '/translation-stats.php';
}

tests_add_filter( 'muplugins_loaded', 'translation_stats_manually_load_plugin' );

// Start up the WP testing environment.
require "{$translation_stats_tests_dir}/includes/bootstrap.php";
