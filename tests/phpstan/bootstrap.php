<?php
/**
 * PHPStan bootstrap file
 *
 * @package Translation_Stats
 */

// Set Translation Stats plugin version.
if ( ! defined( 'TRANSLATION_STATS_VERSION' ) ) {
	define( 'TRANSLATION_STATS_VERSION', '1.2.5' );
}

// Set Translation Stats required PHP version. Needed for PHP compatibility check for WordPress < 5.1.
if ( ! defined( 'TRANSLATION_STATS_REQUIRED_PHP' ) ) {
	define( 'TRANSLATION_STATS_REQUIRED_PHP', '7.4' );
}


// Require plugin main file.
require_once 'translation-stats.php';
