# Translation Stats: guide for coding agents

WordPress plugin that shows the translation stats of the installed plugins and themes, from [translate.wordpress.org](https://translate.wordpress.org/), in the WordPress admin.

* Requires PHP 7.4 and WordPress 5.9 (see the plugin header in `translation-stats.php`).
* Node.js version in `.nvmrc`.
* Text domain `translation-stats`. Namespace `Translation_Stats`.

## Layout

| Path | Contents |
| --- | --- |
| `translation-stats.php` | Plugin file: headers, constants, PHP version check, class autoloader. |
| `includes/` | One class per file, `Translation_Stats\Class_Name` in `class-class-name.php`, loaded by the autoloader. |
| `assets/src/` | JavaScript and SCSS sources. |
| `assets/build/` | Built assets, **committed**. Rebuild with `npm run build` after changing the sources. |
| `assets/lib/` | Third party libraries (GlotPress Locales). |
| `tests/phpunit/` | PHPUnit tests, `includes/test-*.php` for the classes, test themes in `data/`. |
| `tests/js/` | Vitest tests, `*.test.js`. |
| `uninstall.php` | Deletes the plugin data, if set in the settings. |

### Main classes

* `Translation_Stats`: initializes the plugin, registers the assets and the REST API routes.
* `Project`, with `Plugin_Project` and `Theme_Project`: a translate.WordPress.org project, its subprojects, translate URLs and stats, cached in transients. Themes have no subprojects: their stats are in the project itself.
* `Plugins`: the Plugins screen column. `Themes`: the Themes screen data.
* `Stats_Bar` and `Stats_Widget`: the stats widget parts shared by plugins and themes.
* `Projects_REST_Controller`: `translation-stats/v1/(plugins|themes)/<slug>/stats`, the stats widget content.
* `Settings`, `Settings_Section_*` and `Settings_Field_*`: the settings page tabs, sections and fields.
* `DB_Update`: the settings format migrations.
* `Utils::get_options()`: the settings, typed. Use it instead of `get_option( TRANSLATION_STATS_WP_OPTION )`.

## Commands

| Command | Does |
| --- | --- |
| `composer install` and `npm ci` | Install the dependencies. |
| `npm run build` | Build the assets with `@wordpress/scripts`. `npm run start` to watch. |
| `composer lint:php` | PHP_CodeSniffer, WordPress coding standards (`phpcs.xml.dist`). `composer format:php` fixes. |
| `composer lint:phpstan` | PHPStan, level 9 (`phpstan.neon.dist`). |
| `composer compat:php` | PHP compatibility from 7.4. |
| `composer lint:phpmd` | PHPMD size and complexity rules, also checked by Codacy. |
| `npm run lint:js` and `npm run lint:css` | ESLint, with the `@wordpress/scripts` default config and its Prettier formatting, and Stylelint. |
| `npm run format:js` | Format the JavaScript with Prettier. |
| `npm run lint:md:docs` | Markdown lint. |
| `composer test` | PHPUnit, single site. `composer test:multisite` for multisite. |
| `npm run test:unit` | Vitest. `npm run test:unit:coverage` for the coverage. |

### PHPUnit setup

* Install the WordPress tests library with `bin/install-wp-tests.sh <db-name> <db-user> <db-pass> <db-host> <wp-version>`, in a **dedicated test database**: the tests empty it.
* Set `WP_TESTS_DIR` to the tests library, and `WP_TESTS_PHPUNIT_POLYFILLS_PATH` to `vendor/yoast/phpunit-polyfills`.
* The tests mock the translate.WordPress.org API. The `wordpress-translate-api` group makes real requests: exclude it with `--exclude-group wordpress-translate-api`, as CI does except in one job.
* Don't depend on the bundled plugins or themes of each WordPress version: their headers change between versions. Use fixtures, like the test themes in `tests/phpunit/data/themes`.

## Conventions

* Follow the WordPress coding standards, and the code around: comment density, naming, docblocks.
* Docblocks with `@since` for the new and changed methods. New code in this version uses `@since 2.0.0`.
* PHPStan level 9 must pass: check the types instead of casting `mixed`.
* Escape the output, sanitize the input, check the capabilities and nonces.
* Don't print anything before the admin page output: on `plugins_loaded`, in REST and Ajax requests. Show the admin notices on `admin_notices`.
* New settings, or changes to the saved settings format, need a `DB_Update` migration and a new `TRANSLATION_STATS_SETTINGS_VERSION`.
* Transients use `TRANSLATION_STATS_TRANSIENTS_PLUGINS_PREFIX` or `TRANSLATION_STATS_TRANSIENTS_THEMES_PREFIX`, to be cleaned and deleted on uninstall.
* Keep the hooks, the REST API routes and the public methods compatible, or note the change in the changelog.
* New files that aren't part of the plugin go in `.distignore` and `.gitattributes` (`export-ignore`).

## Tests and pull requests

* New and changed code needs tests: the Codecov patch coverage target is 80%. The project coverage is informational.
* Run PHPUnit on the minimum PHP (7.4) and the latest PHP, single site and multisite, before pushing.
* Run the linters and the tests of the changed code, and rebuild the assets if the sources changed: CI checks that the build is up to date.
* In the pull request description, separate the test plan in **Locally** and **CI**, and tick CI only after it passes.
