<?php
/**
 * Class file for the Translation Stats block attributes.
 *
 * @package Translation_Stats
 *
 * @since 2.1.0
 */

namespace Translation_Stats;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit; // @codeCoverageIgnore
}

if ( ! class_exists( __NAMESPACE__ . '\Stats_Block_Attributes' ) ) {

	/**
	 * Class Stats_Block_Attributes.
	 *
	 * The project, language and subprojects of the Translation Stats block, from its attributes.
	 *
	 * @phpstan-type BlockAttributes array{type?: mixed, slug?: mixed, name?: mixed, language?: mixed, subprojects?: mixed, showName?: mixed, showTitle?: mixed, showCounts?: mixed}
	 */
	class Stats_Block_Attributes {

		/**
		 * Get the project of the block.
		 *
		 * @since 2.1.0
		 *
		 * @param BlockAttributes $attributes   Block attributes.
		 *
		 * @return Project|null   The plugin or theme project, or null without slug.
		 */
		public static function project( $attributes ) {

			$slug = isset( $attributes['slug'] ) && is_string( $attributes['slug'] ) ? sanitize_title( $attributes['slug'] ) : '';

			if ( '' === $slug ) {
				return null;
			}

			$type = $attributes['type'] ?? 'plugins';

			return 'themes' === $type ? new Theme_Project( $slug ) : new Plugin_Project( $slug );
		}

		/**
		 * Get the project name: the block name, the installed plugin or theme name, or the slug.
		 *
		 * @since 2.1.0
		 *
		 * @param Project         $project      The plugin or theme project.
		 * @param BlockAttributes $attributes   Block attributes.
		 *
		 * @return string   Project name.
		 */
		public static function project_name( $project, $attributes ) {

			if ( isset( $attributes['name'] ) && is_string( $attributes['name'] ) && '' !== trim( $attributes['name'] ) ) {
				return trim( $attributes['name'] );
			}

			if ( 'themes' === $project->get_type() ) {
				$theme = wp_get_theme( $project->get_slug() );
				return $theme->exists() ? (string) $theme->get( 'Name' ) : $project->get_slug();
			}

			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php'; // @codeCoverageIgnore
			}

			foreach ( get_plugins() as $plugin_file => $plugin ) {
				if ( Translations_API::get_plugin_slug( $plugin_file ) === $project->get_slug() ) {
					return $plugin['Name'];
				}
			}

			return $project->get_slug();
		}

		/**
		 * Get the translate.wordpress.org URL of the project in the Locale.
		 *
		 * @since 2.1.0
		 *
		 * @param Project $project   The plugin or theme project.
		 * @param Locale  $locale    The Locale.
		 *
		 * @return string   Project URL in the Locale, as the language link of the plugins and themes widgets.
		 */
		public static function locale_project_url( $project, $locale ) {
			return 'https://translate.wordpress.org/locale/' . $locale->locale_slug . '/wp-' . $project->get_type() . '/' . $project->get_slug();
		}

		/**
		 * Get the WordPress.org Locale of the block language, without loading the available translations from WordPress.org.
		 *
		 * @since 2.1.0
		 *
		 * @param BlockAttributes $attributes   Block attributes.
		 *
		 * @return Locale|null   The Locale, or null if not found.
		 */
		public static function locale( $attributes ) {

			$wp_locale = isset( $attributes['language'] ) && is_string( $attributes['language'] ) && '' !== $attributes['language'] ? $attributes['language'] : get_locale();

			foreach ( Locales::instance()->locales as $locale ) {
				if ( 'en' !== $locale->slug && $locale->wp_locale === $wp_locale ) {
					return $locale;
				}
			}

			return null;
		}

		/**
		 * Get the subprojects to show: the selected plugin subprojects, or the theme project, with the block project name.
		 *
		 * @since 2.1.0
		 *
		 * @param Project         $project      The plugin or theme project.
		 * @param BlockAttributes $attributes   Block attributes.
		 *
		 * @return list<array{slug: string, name: string}>   Subprojects.
		 */
		public static function subprojects( $project, $attributes ) {

			// The theme project bar, with the block project name, as the theme may not be installed.
			if ( 'themes' === $project->get_type() ) {
				return array(
					array(
						'slug' => '',
						'name' => self::project_name( $project, $attributes ),
					),
				);
			}

			$selected = isset( $attributes['subprojects'] ) && is_array( $attributes['subprojects'] ) ? $attributes['subprojects'] : array( 'stable', 'stable-readme' );

			return array_values(
				array_filter(
					$project->get_subprojects(),
					function ( $subproject ) use ( $selected ) {
						return in_array( $subproject['slug'], $selected, true );
					}
				)
			);
		}

		/**
		 * Get the block ID, to scope the stats bars width, from the project and language.
		 * Unique also in the block editor, that renders each block in its own request.
		 * Blocks with the same project and language have the same bars width.
		 *
		 * @since 2.1.0
		 *
		 * @param Project $project   The plugin or theme project.
		 * @param Locale  $locale    The Locale.
		 *
		 * @return string   Block ID.
		 */
		public static function block_id( $project, $locale ) {
			return 'translation-stats-block-' . substr( md5( implode( '/', Stats_Block_Cache::refresh_args( $project, $locale ) ) ), 0, 12 );
		}
	}
}
