<?php
/**
 * Translation Stats
 *
 * @package      Translation Stats
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
 * Version:           0.9.5.3
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
define( 'TSTATS_VERSION', '0.9.5.2' );

// Set Translation Stats required PHP version. Needed for PHP compatibility check for WordPress < 5.1.
define( 'TSTATS_REQUIRED_PHP', '5.6' );

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

// Set Translation Stats plugin URL.
define( 'TSTATS_DIR_URL', plugin_dir_url( __FILE__ ) );

// Set Translation Stats plugin filesystem path.
define( 'TSTATS_DIR_PATH', plugin_dir_path( __FILE__ ) );

// Set Translation Stats file path.
define( 'TSTATS_FILE', plugin_basename( __FILE__ ) );

// Set Translation Stats Debug ( true / false ).
// Use 'tstats_enable_debug' to enable debug ( e.g. add_filter( 'tstats_enable_debug', '__return_true' ) ).
define( 'TSTATS_DEBUG', apply_filters( 'tstats_enable_debug', false ) );


// Check for PHP compatibility.
// Adapted from https://pento.net/2014/02/18/dont-let-your-plugin-be-activated-on-incompatible-sites/.
add_action( 'admin_init', 'tstats_check_version' );


// Stop running the plugin if on an incompatible PHP version.
if ( ! tstats_compatible_version() ) {
	return;
}


/**
 * Backup sanity check, in case the plugin is activated, or the versions change after activation.
 * WordPress 5.1 news: https://wordpress.org/news/2019/04/minimum-php-version-update/.
 *
 * If incompatible, deactivate the plugin and add an admin notice.
 *
 * @since 0.9.4.3
 */
function tstats_check_version() {

	if ( ! tstats_compatible_version() ) {

		if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {

			// Deactivate the plugin.
			deactivate_plugins( plugin_basename( __FILE__ ) );

			// Show disabled admin notice.
			add_action( 'admin_notices', 'tstats_disabled_notice' );

		}
	}
}


/**
 * Show disabled notice with the minimum required PHP version.
 * Adapted from https://pento.net/2014/02/18/dont-let-your-plugin-be-activated-on-incompatible-sites/.
 *
 * @since 0.9.4.3
 */
function tstats_disabled_notice() {

	// Get plugin data.
	$plugin_data = get_plugin_data( __FILE__ );
	?>

	<div class="notice notice-error is-dismissible">
		<p>

			<?php
			printf(
				/* translators: 1: Plugin file, 2: Error message. */
				wp_kses_post( 'The plugin %1$s has been deactivated due to an error: %2$s', 'translation-stats' ),
				'<strong>' . esc_html( $plugin_data['Name'] ) . '</strong>',
				esc_html__( 'This plugin doesn&#8217;t work with your version of PHP.', 'translation-stats' )
			);
			?>

		</p>
		<p>

			<?php
			printf(
				/* translators: %s Minimum PHP version required. */
				esc_html__( 'Requires PHP version %s or higher.', 'translation-stats' ),
				esc_html( TSTATS_REQUIRED_PHP )
			);

			// Show aditional update link if on WP version 5.1 or higher.
			// Capability added in WP 5.1: https://core.trac.wordpress.org/ticket/44457.
			// Introduced in WP 5.1: https://developer.wordpress.org/reference/functions/wp_get_update_php_url/.
			if ( current_user_can( 'update_php' ) && version_compare( $GLOBALS['wp_version'], '5.1', '>=' ) ) {
				printf(
					/* translators: %s: URL to Update PHP page. */
					' ' . wp_kses_post( '<a href="%s">Learn more about updating PHP</a>.', 'translation-stats' ),
					esc_url( wp_get_update_php_url() )
				);
			}
			?>

		</p>
	</div>

	<?php
}


/**
 * Check Translation Stats minimum requirements.
 * Adapted from https://pento.net/2014/02/18/dont-let-your-plugin-be-activated-on-incompatible-sites/.
 *
 * @since 0.9.4.3
 *
 * @return bool
 */
function tstats_compatible_version() {

	// Check minimum required PHP version.
	if ( version_compare( PHP_VERSION, TSTATS_REQUIRED_PHP, '<=' ) ) {
		return false;
	}

	return true;
}

// Include Composer autoload.
require_once dirname( __FILE__ ) . '/vendor/autoload.php';

// Include class files used by the plugin.
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-main.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-globals.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-notices.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-transients.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-translations-api.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-settings-sidebar.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-settings-widgets.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-settings-footer.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-settings-api.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-settings-plugins.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-settings.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-gettext-jedgenerator.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-gettext.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-update-translations.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-update-core.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-plugins.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-tstats-debug.php';
