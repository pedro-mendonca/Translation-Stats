# Translation Stats #

[![Build Status](https://travis-ci.org/pedro-mendonca/Translation-Stats.svg?branch=master)](https://travis-ci.org/pedro-mendonca/Translation-Stats)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/bcd1b44a1d6542e2b75b7b479ce56804)](https://www.codacy.com/app/pedro-mendonca/Translation-Stats?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=pedro-mendonca/Translation-Stats&amp;utm_campaign=Badge_Grade)
[![RIPS CodeRisk](https://coderisk.com/wp/plugin/translation-stats/badge "RIPS CodeRisk")](https://coderisk.com/wp/plugin/translation-stats)
[![PHP from Travis config](https://img.shields.io/travis/php-v/pedro-mendonca/Translation-Stats.svg)](https://travis-ci.org/pedro-mendonca/Translation-Stats)
[![Wordpress Plugin: Tested WP Version](https://img.shields.io/wordpress/plugin/tested/translation-stats.svg)](https://wordpress.org/plugins/translation-stats/advanced/)
[![Wordpress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/translation-stats.svg)](https://wordpress.org/plugins/translation-stats/advanced/)
[![Donate](https://img.shields.io/badge/Donate-PayPal-blue.svg)](https://paypal.me/pedromendonca/)

**Contributors:** pedromendonca  
**Donate link:** [paypal.me/pedromendonca](https://paypal.me/pedromendonca/)  
**Tags:** internationalization, i18n, localization, l10n, translation, statistics, glotpress, dark mode  
**Requires at least:** 4.9  
**Tested up to:** 5.0.2  
**Requires PHP:** 5.4  
**Stable tag:** 0.8.0  
**License:** GPLv2 or later  
**License URI:** [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html)  

Show the WordPress.org translation stats in your installed plugins list.

## Description ##

The plugins adds a "Translation Stats" column to the plugin list screen in WordPress admin.

The translation stats are shown only for plugins that exist in [translate.wordpress.org](https://translate.wordpress.org/) and are [prepared for localization](https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/).

![GlotPress](./assets/banner-772x250.png)

If a plugin doesn't have complete translation stats, you'll see a notice with one of the reasons bellow:
-   Plugin not found on WordPress.org
-   Translation project not found on WordPress.org
-   The plugin is not properly prepared for localization

The color scheme of the translation stats bars is exactly same used in [GlotPress](https://wordpress.org/plugins/glotpress/) for consistency with the translation experience in [translate.wp.org](https://translate.wordpress.org/).

This plugin is properly prepared for localization.

## Frequently Asked Questions ##

### What are the plugin subprojects? ###

| Subproject             | Description                                                                                                |
| ---                    | ---                                                                                                        |
| **Development**        | Translation of the *Development* version of the plugin                                                     |
| **Development Readme** | Readme of the *Development* version of the plugin page on [wp.org/plugins](https://wordpress.org/plugins/) |
| **Stable**             | Translation of the *Stable* version of the plugin                                                          |
| **Stable Readme**      | Readme of the *Stable* version of the plugin page on [wp.org/plugins](https://wordpress.org/plugins/)      |

### How can I translate a plugin? ###
-   Register and login in [wp.org](https://login.wordpress.org/)
-   Click on the plugin subproject stats bar you want to translate
-   Read the Polyglots [Translator’s Handbook](https://make.wordpress.org/polyglots/handbook/)
-   Translate according the rules of your [Locale Translation Team](https://make.wordpress.org/polyglots/teams/)

### Should I translate both Stable and Development? ###
Since mid-April 2016, not only new strings but also edits are synced between dev and stable (both ways, only approved translations). When a plugin releases a new version all translations are copied from dev to stable. [Read more...](https://make.wordpress.org/polyglots/handbook/frequently-asked-questions/#should-i-translate-both-stable-and-dev)

### Is this plugin compatible with Dark Mode? ###
Yes, Translation Stats includes a color scheme that works specifically with the plugin [Dark Mode](https://wordpress.org/plugins/dark-mode/).

### Can I help translating this plugin to my own language? ###
Yes you can! If you want to translate this plugin to your language, please [click here](https://translate.wordpress.org/projects/wp-plugins/translation-stats).

### Can I contribute to this plugin? ###
Sure! You are welcome to report any issues or add feature suggestions on the [GitHub repository](https://github.com/pedro-mendonca/translation-stats).

## Screenshots ##

1. Translation Stats shown in WordPress plugins page
![screenshot-1](./assets/screenshot-1.png)

2. Translation Stats installed plugins list
![screenshot-2](./assets/screenshot-2.png)

3. Translation Stats settings tab
![screenshot-3](./assets/screenshot-3.png)

4. Translation Stats tools tab
![screenshot-4](./assets/screenshot-4.png)

## Changelog ##

### 0.8 ###
-   Add Translation Stats settings page
-   Add installed plugins table to select only the plugins you want to manage translation stats to reduce http requests
-   Add option to enable or disable warnings of translation projects
-   Add options to reset settings and clean cache to force update the translation stats
-   Add option to specify the translation language you want
-   Add option to choose to keep or delete plugin data on uninstall
-   Add uninstall file

### 0.7 ###
-   Fix Translation Stats column show/hide logic
-   Use [WordPress Coding Standards](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards)

### 0.6 ###
-   Add WordPress core notices styles to error messages
-   Add assets
-   Minor code improvements
-   Readme update

### 0.5 ###
-   Initial release.
