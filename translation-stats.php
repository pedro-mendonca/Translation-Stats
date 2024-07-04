<?php
/**
 * Translation Stats
 *
 * @package           Translation_Stats
 * @link              https://github.com/pedro-mendonca/Translation-Stats
 * @author            Pedro Mendonça
 * @copyright         2018 Pedro Mendonça
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Translation Stats
 * Plugin URI:        https://translationstats.com
 * GitHub Plugin URI: https://github.com/pedro-mendonca/Translation-Stats
 * Description:       Show plugins translation stats on your WordPress install.
 * Version:           1.3.1
 * Requires at least: 4.9
 * Tested up to:      6.6
 * Requires PHP:      7.4
 * Author:            Pedro Mendonça
 * Author URI:        https://translationstats.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       translation-stats
 * Domain Path:       /languages
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if get_plugin_data() function exists.
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Get plugin headers data.
$translation_stats_data = get_plugin_data( __FILE__, false, false );

// Set Translation Stats plugin version.
if ( ! defined( 'TRANSLATION_STATS_VERSION' ) ) {
	define( 'TRANSLATION_STATS_VERSION', $translation_stats_data['Version'] );
}

// Set Translation Stats required PHP version. Needed for PHP compatibility check for WordPress < 5.1.
if ( ! defined( 'TRANSLATION_STATS_REQUIRED_PHP' ) ) {
	define( 'TRANSLATION_STATS_REQUIRED_PHP', $translation_stats_data['RequiresPHP'] );
}

// Set Translation Stats settings database version.
define( 'TRANSLATION_STATS_SETTINGS_VERSION', '1.0' );

// Set the WordPress option to store Translation Stats settings.
define( 'TRANSLATION_STATS_WP_OPTION', 'tstats_settings' );

// Set Translation Stats settings page slug.
define( 'TRANSLATION_STATS_SETTINGS_PAGE', 'translation-stats' );

// Set Translation Stats settings sections prefix.
define( 'TRANSLATION_STATS_SETTINGS_SECTIONS_PREFIX', 'translation_stats_' );

// Set Translation Stats transients prefix.
define( 'TRANSLATION_STATS_TRANSIENTS_PREFIX', 'translation_stats_plugin_' );

// Set Translation Stats transients default 24h expiration for Translations data.
define( 'TRANSLATION_STATS_TRANSIENTS_TRANSLATIONS_EXPIRATION', DAY_IN_SECONDS );

// Set Translation Stats transients 1 week expiration for Locales data.
define( 'TRANSLATION_STATS_TRANSIENTS_LOCALES_EXPIRATION', WEEK_IN_SECONDS );

// Set Translation Stats plugin URL.
define( 'TRANSLATION_STATS_DIR_URL', plugin_dir_url( __FILE__ ) );

// Set Translation Stats plugin filesystem path.
define( 'TRANSLATION_STATS_DIR_PATH', plugin_dir_path( __FILE__ ) );

// Set Translation Stats file path.
define( 'TRANSLATION_STATS_FILE', plugin_basename( __FILE__ ) );


/**
 * Require wordpress.org Locales list since translate.wp.org Languages API (https://translate.wordpress.org/api/languages/) was disabled on meta changeset #10056 (https://meta.trac.wordpress.org/changeset/10056).
 * Copy of https://meta.trac.wordpress.org/browser/sites/trunk/wordpress.org/public_html/wp-content/mu-plugins/pub/locales/locales.php
 *
 * Updated on 2020-06-28.
 */
require_once 'assets/lib/locales/locales.php';


// Check for PHP compatibility.
// Adapted from https://pento.net/2014/02/18/dont-let-your-plugin-be-activated-on-incompatible-sites/.
add_action( 'admin_init', __NAMESPACE__ . '\translation_stats_check_version' );


// Stop running the plugin if on an incompatible PHP version.
if ( ! translation_stats_compatible_version() ) {
	return;
}


/**
 * Backup sanity check, in case the plugin is activated, or the versions change after activation.
 * WordPress 5.1 news: https://wordpress.org/news/2019/04/minimum-php-version-update/.
 *
 * If incompatible, deactivate the plugin and add an admin notice.
 *
 * @since 0.9.4.3
 * @since 1.2.0   Renamed from tstats_check_version() to translation_stats_check_version().
 *
 * @return void
 */
function translation_stats_check_version() {

	if ( ! translation_stats_compatible_version() ) {

		if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {

			// Deactivate the plugin.
			deactivate_plugins( plugin_basename( __FILE__ ) );

			// Show disabled admin notice.
			add_action( 'admin_notices', __NAMESPACE__ . '\translation_stats_disabled_notice' );

		}
	}
}


/**
 * Show disabled notice with the minimum required PHP version.
 * Adapted from https://pento.net/2014/02/18/dont-let-your-plugin-be-activated-on-incompatible-sites/.
 *
 * @since 0.9.4.3
 * @since 1.2.0   Renamed from tstats_disabled_notice() to translation_stats_disabled_notice().
 *
 * @return void
 */
function translation_stats_disabled_notice() {

	// Get plugin data.
	$plugin_data = get_plugin_data( __FILE__ );
	?>

	<div class="notice notice-error is-dismissible">
		<p>

			<?php
			printf(
				wp_kses_post(
					/* translators: 1: Plugin name. 2: Error message. */
					__( 'The plugin %1$s has been deactivated due to an error: %2$s', 'translation-stats' )
				),
				'<code>' . esc_html( $plugin_data['Name'] ) . '</code>',
				esc_html__( 'This plugin doesn&#8217;t work with your version of PHP.', 'translation-stats' )
			);
			?>

		</p>
		<p>

			<?php
			printf(
				/* translators: %s: Minimum PHP version required. */
				esc_html__( 'Requires PHP version %s or higher.', 'translation-stats' ),
				esc_html( TRANSLATION_STATS_REQUIRED_PHP )
			);

			// Show additional update link if on WP version 5.1 or higher.
			// Capability added in WP 5.1: https://core.trac.wordpress.org/ticket/44457.
			// Introduced in WP 5.1: https://developer.wordpress.org/reference/functions/wp_get_update_php_url/.
			if ( current_user_can( 'update_php' ) && version_compare( $GLOBALS['wp_version'], '5.1', '>=' ) ) {
				echo ' ' . sprintf(
					wp_kses_post(
						/* translators: %s: URL to Update PHP page. */
						__( '<a href="%s">Learn more about updating PHP</a>.', 'translation-stats' )
					),
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
 * @since 1.2.0   Renamed from tstats_compatible_version() to translation_stats_compatible_version().
 *
 * @return bool
 */
function translation_stats_compatible_version() {

	// Check minimum required PHP version.
	return version_compare( PHP_VERSION, TRANSLATION_STATS_REQUIRED_PHP, '>=' );
}


/**
 * Register classes autoloader function.
 *
 * @since 0.9.6
 *
 * @param callable(string): void
 */
spl_autoload_register( __NAMESPACE__ . '\class_autoload' );


/**
 * Class autoloader.
 *
 * @since 0.9.6
 * @since 1.1.0  Remove namespace from class name.
 * @since 1.1.1  Check if class exist in project namespace.
 *
 * @param string $class_name   Class name.
 *
 * @return void
 */
function class_autoload( $class_name ) {

	$project_namespace = __NAMESPACE__ . '\\';

	// Check if class is in the project namespace.
	if ( 0 !== strncmp( $project_namespace, $class_name, strlen( $project_namespace ) ) ) {
		return;
	}

	// Set class file full path.
	$class = sprintf(
		'%sincludes/class-%s.php',
		TRANSLATION_STATS_DIR_PATH,
		str_replace( '_', '-', strtolower( str_replace( $project_namespace, '', $class_name ) ) )
	);

	if ( ! is_file( $class ) ) {
		return;
	}

	require_once $class;
}


// Initialize the plugin.
new Translation_Stats();
