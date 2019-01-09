<?php
/**
 * Translation Stats
 *
 * @package      TranslationStats
 * @author       Pedro Mendonça
 * @copyright    2018 Pedro Mendonça
 * @license      GPL2
 *
 * @wordpress-plugin
 * Plugin Name:       Translation Stats
 * Plugin URI:        https://github.com/pedro-mendonca/translation-stats
 * GitHub Plugin URI: https://github.com/pedro-mendonca/translation-stats
 * Description:       Show WordPress.org translation stats in your installed plugins list.
 * Version:           0.8.0
 * Author:            Pedro Mendonça
 * Author URI:        https://pedromendonca.pt
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
define( 'TSTATS_VERSION', '0.8.0' );

// Set the WordPress option to store Translation Stats settings.
define( 'TSTATS_WP_OPTION', 'tstats_settings' );

// Set Translation Stats settings page slug.
define( 'TSTATS_SETTINGS_PAGE', 'translation-stats' );

// Set Translation Stats transients prefix.
define( 'TSTATS_TRANSIENTS_PREFIX', 'translation_stats_plugin_' );

// Set Translation Stats plugin path.
define( 'TSTATS_PATH', plugin_dir_url( __FILE__ ) );

// Set Translation Stats plugin path.
define( 'TSTATS_FILE', plugin_basename( __FILE__ ) );

// Set Translation Stats Debug ( true / false ).
define( 'TSTATS_DEBUG', false );


// Include class files used by our plugin.
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-main.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-notices.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-transients.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-translate-api.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-debug.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-settings-api.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-settings-plugins.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-settings.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-plugins.php';
