<?php
/**
 * Translation Stats
 *
 * @package      TranslationStats
 * @link         https://github.com/pedro-mendonca/Translation-Stats
 * @author       Pedro Mendonça
 * @copyright    2018 Pedro Mendonça
 * @license      GPL2
 *
 * @wordpress-plugin
 * Plugin Name:       Translation Stats
 * Plugin URI:        https://translationstats.com
 * GitHub Plugin URI: https://github.com/pedro-mendonca/Translation-Stats
 * Description:       Show plugins translation stats on your WordPress install.
 * Version:           0.8.5
 * Author:            Pedro Mendonça
 * Author URI:        https://translationstats.com
 * License:           GPL2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       translation-stats
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Set Translation Stats plugin version.
define( 'TSTATS_VERSION', '0.8.5' );

// Set the WordPress option to store Translation Stats settings.
define( 'TSTATS_WP_OPTION', 'tstats_settings' );

// Set Translation Stats settings page slug.
define( 'TSTATS_SETTINGS_PAGE', 'translation-stats' );

// Set Translation Stats transients prefix.
define( 'TSTATS_TRANSIENTS_PREFIX', 'translation_stats_plugin_' );

// Set Translation Stats transients default 24h expiration for Translations data.
define( 'TSTATS_TRANSIENTS_TRANSLATIONS_EXPIRATION', DAY_IN_SECONDS );

// Set Translation Stats transients 1 week expiration for Locales data.
define( 'TSTATS_TRANSIENTS_LOCALES_EXPIRATION', WEEK_IN_SECONDS );

// Set Translation Stats plugin path.
define( 'TSTATS_PATH', plugin_dir_url( __FILE__ ) );

// Set Translation Stats plugin path.
define( 'TSTATS_FILE', plugin_basename( __FILE__ ) );

// Set Translation Stats Debug ( true / false ).
// Use 'tstats_enable_debug' to enable debug:
// add_filter( 'tstats_enable_debug', '__return_true' );
define( 'TSTATS_DEBUG', apply_filters( 'tstats_enable_debug', false ) );


// Include class files used by our plugin.
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-main.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-globals.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-notices.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-transients.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-translations-api.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-settings-api.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-settings-plugins.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-settings.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-plugins.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-debug.php';
