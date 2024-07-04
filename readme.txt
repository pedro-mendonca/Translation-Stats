=== Translation Stats ===
Contributors: pedromendonca
Donate link: https://github.com/sponsors/pedro-mendonca
Tags: internationalization, i18n, localization, l10n, translation
Requires at least: 4.9
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.3.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Show plugins translation stats on your WordPress install.

== Description ==

The plugin adds a "Translation Stats" column to the plugin list screen in WordPress admin.

The translation stats are shown only for plugins that exist in [translate.wordpress.org](https://translate.wordpress.org/) and are [prepared for localization](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/).

If a plugin doesn't have complete translation stats, you'll see a notice with one of the reasons below:

*   Plugin not found on WordPress.org
*   Translation project not found on WordPress.org
*   The plugin is not properly prepared for localization

The color scheme of the translation stats bars is exactly same used in [GlotPress](https://wordpress.org/plugins/glotpress/) for consistency with the translation experience in [translate.wp.org](https://translate.wordpress.org/).

This plugin is properly prepared for localization.

== Frequently Asked Questions ==

= What are the plugin subprojects? =
*   **Development** - Translation of the *Development* version of the plugin
*   **Development Readme** - Readme of the *Development* version of the plugin page on [wp.org/plugins](https://wordpress.org/plugins/)
*   **Stable** - Translation of the *Stable* version of the plugin
*   **Stable Readme** - Readme of the *Stable* version of the plugin page on [wp.org/plugins](https://wordpress.org/plugins/)

= How can I translate a plugin? =
*   Register and login in [wp.org](https://login.wordpress.org/)
*   Click on the plugin subproject stats bar you want to translate
*   Read the Polyglots [Translator’s Handbook](https://make.wordpress.org/polyglots/handbook/)
*   Translate according the rules of your [Locale Translation Team](https://make.wordpress.org/polyglots/teams/)

= Should I translate both Stable and Development? =
Since mid-April 2016, not only new strings but also edits are synced between dev and stable (both ways, only approved translations). When a plugin releases a new version all translations are copied from dev to stable. [Read more...](https://make.wordpress.org/polyglots/handbook/frequently-asked-questions/#should-i-translate-both-stable-and-dev)

= How long does it take for a translation to become available? =
Translations for the readme are published almost immediately.
The language pack for a plugin will be generated when 90% of the Stable (latest release) sub-project strings have been translated and approved.

= Can I help translating this plugin to my own language? =
Yes you can! If you want to translate this plugin to your language, please [click here](https://translate.wordpress.org/projects/wp-plugins/translation-stats).

= Can I contribute to this plugin? =
Sure! You are welcome to report any issues or add feature suggestions on the [GitHub repository](https://github.com/pedro-mendonca/Translation-Stats).

== Screenshots ==

1. Translation Stats shown in WordPress plugins page
2. Translation Stats installed plugins list
3. Translation Stats settings tab
4. Translation Stats tools tab

== Changelog ==

= Unreleased =
*   Add PHP Unit Tests.
*   Deprecate TRANSLATION_STATS_DEBUG constant.

= 1.3.1 =
*   Tested up to WP 6.6
*   Always show notices for plugins not prepared for localization.

= 1.3.0 =
*   New checkboxes in the subprojects column to bulk-select a specific subproject in all plugins.
*   Improve the indeterminate [-] behavior of table checkboxes.
*   Cleanup the Debug section and fix the PHP error when there are no settings saved yet.

= 1.2.8 =
*   Fix deprecation notice on PHP 8.2+
*   Show current/all counts on subproject statistics. Props @Irinashl

= 1.2.7 =
*   Fix typos in comments. Props @szepeviktor
*   Show stats admin notices only on `WP_DEBUG`. Props @Irinashl

= 1.2.6 =
*   Tested up to WP 6.5
*   Fix incorrect position of placeholders in comments to translators. Props @presskopp

= 1.2.5 =
*   Custom icon for queued update notice

= 1.2.4 =
*   Queue Translation Stats updates in sequence to avoid many Ajax requests in parallel

= 1.2.3 =
*   Fix wrong count on plugins filter by only counting plugins enabled on Translation Stats settings that are actually installed

= 1.2.2 =
*   Tested up to WP 6.1
*   Tested only on supported PHP versions (7.4+)
*   More detailed console log for updating plugin translation stats
*   Only show language pack information if percent translated is below minimum threshold

= 1.2.1 =
*   Tested up to WP 5.9
*   Fix PHP error on new installs before saving settings
*   Fix delete plugin data on uninstall
*   Use minified CSS

= 1.2.0 =
*   Stats widget color and border improvements
*   Stats widget notices improved with the WP.org information about translation projects issues
*   Code refactoring of settings sections and fields for easy management and extension
*   Code refactoring of the Stats widget bars and notices
*   Admin notices custom wrap support
*   Fix code prefixes
*   Fix return error on disabled subproject stats bar
*   Fix CSS issues
*   Debug mode improvements
*   Linting and compiling improved

= 1.1.5 =
*   GitHub release process optimization
*   Assets folders optimization
*   Minor code improvements

= 1.1.4 =
*   Remove unnecessary development files

= 1.1.3 =
*   Tested up to WP 5.8
*   Remove deprecated dark-mode compatibility

= 1.1.2 =
*   Tested up to WP 5.7
*   Minor code improvements
*   Update plugin constants prefixes

= 1.1.1 =
*   Tested up to WP 5.6
*   Minor code improvements

= 1.1.0 =
*   Include Locales list since [translate.wp.org Languages API](https://translate.wordpress.org/api/languages/) was disabled on meta [changeset #10056](https://meta.trac.wordpress.org/changeset/10056)
*   Remove recent feature to Update WordPress Translation, feature moved to the new spin-off plugin [Translation Tools](https://wordpress.org/plugins/translation-tools/), created specifically to deal with translations updates, with many related features out of the scope of this plugin
*   Tested up to WP 5.5
*   Code optimization

= 1.0.5 =
*   Add WPLANG and available languages to the settings languages dropdown

= 1.0.4 =
*   Fix admin notices
*   Minor code improvements

= 1.0.3 =
*   Add footer custom message
*   Fix utm parameters

= 1.0.2 =
*   New sort and filter in the settings plugins table
*   Fix timeout error on get languages list from translate.wp.org API

= 1.0.1 =
*   Fix custom column title icon and screen reader text

= 1.0.0 =
*   Version 1.0! 🎉
*   New settings database structure and version
*   New settings database updater
*   New activation class
*   Optimized plugins settings table
*   Fix notices not showing when "Warnings" settings was disabled
*   Minor code improvements

= 0.9.9 =
*   New plugins View to show only Translation Stats enabled plugins
*   New link to filtered plugins list in settings page
*   New link to settings page in plugins page
*   Fix translation stats not showing when "Warnings" settings was disabled
*   Minor code improvements
*   Tested up to WP 5.4

= 0.9.8 =
*   Fix settings checkboxes tristate bug
*   Remove filter 'tstats_enable_debug'
*   Fix WP core update translation notice on Beta install
*   Refactoring with PHPStan (Level 6)

= 0.9.7 =
*   Add tristate to parent checkboxes with enabled and disabled children
*   Minor code improvements

= 0.9.6.3 =
*   Fix JavaScript bug

= 0.9.6.2 =
*   Code optimization

= 0.9.6.1 =
*   Fix admin notices
*   Fix CSS issues

= 0.9.6 =
*   Add Slug and Text Domain check to plugins settings table
*   Add class autoloader
*   Add PHPStan check

= 0.9.5.5 =
*   Fix i18n issue (Thanks @szepeviktor)
*   Minor code improvements
*   Coffee ☕

= 0.9.5.4 =
*   Minor code improvements
*   Tested up to WP 5.3.2

= 0.9.5.3 =
*   Minor code improvements

= 0.9.5.2 =
*   Minor code improvements
*   Add admin notice to dashboard

= 0.9.5.1 =
*   Minor code improvements

= 0.9.5 =
*   Now you can update your WordPress translation when you want
*   No more waiting for language packs or your locale to be 100% complete
*   One click to update all WordPress core translation files ( .po, .mo and all .json )
*   Tested up to WP 5.3

= 0.9.4.3 =
*   Tested up to WP 5.2.4
*   Add PHP compatibility check

= 0.9.4.2 =
*   Tested up to WP 5.2.3
*   Code optimization
*   UI improvement

= 0.9.4.1 =
*   Bump PHP minimum required version
*   Code optimization

= 0.9.4 =
*   New AJAX loading and updating features
*   Improved plugins screen loading speed
*   New button to quick update a single plugin stats
*   Tested up to WP 5.2.2
*   Code optimization

= 0.9.3 =
*   Tested up to WP 5.2.1
*   Code optimization

= 0.9.2 =
*   Support for custom WordPress.org Locale Subdomains

= 0.9.1 =
*   Support for current GlotPress 2.x variants
*   Fix typo

= 0.9.0 =
*   Tested up to WP 5.1.1
*   Fix typo
*   Support new locales
*   Support for all locales in translate.wp.org through WordPress API

= 0.8.5 =
*   Tested up to WP 5.1
*   Fix CSS issues
*   Fix WordPress Coding Standards errors

= 0.8.4 =
*   Fix CSS issues

= 0.8.3 =
*   Fix uninstall function

= 0.8.2 =
*   Fix Author links

= 0.8.1 =
*   Add Author column to plugins list - feature suggestion from [Webdados](https://www.webdados.pt/)
*   Add links to plugins and authors
*   Fix WordPress Coding Standards errors

= 0.8 =
*   Add Translation Stats settings page
*   Add installed plugins table to select only the plugins you want to manage translation stats to reduce http requests
*   Add option to enable or disable warnings of translation projects
*   Add options to reset settings and clean cache to force update the translation stats
*   Add option to specify the translation language you want
*   Add option to choose to keep or delete plugin data on uninstall
*   Add uninstall file

= 0.7 =
*   Fix Translation Stats column show/hide logic
*   Use [WordPress Coding Standards](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards)

= 0.6 =
*   Add WordPress core notices styles to error messages
*   Add assets
*   Minor code improvements
*   Readme update

= 0.5 =
*   Initial release.
