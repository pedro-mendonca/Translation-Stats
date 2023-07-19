module.exports = function( grunt ) {

	'use strict';

	// Project configuration
	grunt.initConfig( {

		pkg: grunt.file.readJSON( 'package.json' ),

		addtextdomain: {
			options: {
				textdomain: 'translation-stats',
			},
			update_all_domains: {
				options: {
					updateDomains: true
				},
				src: [ '*.php', '**/*.php', '!\.git/**/*', '!bin/**/*', '!assets/lib/**/*', '!node_modules/**/*', '!vendor/**/*', '!tests/**/*', '!wp-content/*' ]
			}
		},

		wp_readme_to_markdown: {
			your_target: {
				files: {
					'README.md': 'readme.txt'
				},
				options: {
					pre_convert: function( readme ) {
						// readme += "\n== My Additional Header ==\n\n My additional text";
						//readme = readme.replace( new RegExp( "(^\\*\\*.+\:\\*\\*.+$\n)", "gim" ), "##################" );

						return readme;
					},
					post_convert: function( readme ) {

						// Custom header.
						var header = "[![Translation Stats banner](./.wordpress-org/banner-1544x500.png)](https://wordpress.org/plugins/translation-stats/)\n";
						header += "\n";
						header += "[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/translation-stats?label=Plugin%20Version&logo=wordpress)](https://wordpress.org/plugins/translation-stats/)";
						header += "[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/stars/translation-stats?label=Plugin%20Rating&logo=wordpress)](https://wordpress.org/support/plugin/translation-stats/reviews/)";
						header += "[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/translation-stats.svg?label=Downloads&logo=wordpress)](https://wordpress.org/plugins/translation-stats/advanced/)";
						header += "[![Sponsor](https://img.shields.io/badge/GitHub-🤍%20Sponsor-ea4aaa?logo=github)](https://github.com/sponsors/pedro-mendonca)";
						header += "\n";
						header += "[![WordPress Plugin Required PHP Version](https://img.shields.io/wordpress/plugin/required-php/translation-stats?label=PHP%20Required&logo=php&logoColor=white)](https://wordpress.org/plugins/translation-stats/)";
						header += "[![WordPress Plugin: Required WP Version](https://img.shields.io/wordpress/plugin/wp-version/translation-stats?label=WordPress%20Required&logo=wordpress)](https://wordpress.org/plugins/translation-stats/)";
						header += "[![WordPress Plugin: Tested WP Version](https://img.shields.io/wordpress/plugin/tested/translation-stats.svg?label=WordPress%20Tested&logo=wordpress)](https://wordpress.org/plugins/translation-stats/)";
						header += "\n";
						header += "[![Coding Standards](https://github.com/pedro-mendonca/Translation-Stats/actions/workflows/coding-standards.yml/badge.svg)](https://github.com/pedro-mendonca/Translation-Stats/actions/workflows/coding-standards.yml)";
						header += "[![Static Analysis](https://github.com/pedro-mendonca/Translation-Stats/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/pedro-mendonca/Translation-Stats/actions/workflows/static-analysis.yml)";
						header += "[![Codacy Badge](https://api.codacy.com/project/badge/Grade/bcd1b44a1d6542e2b75b7b479ce56804)](https://www.codacy.com/app/pedro-mendonca/Translation-Stats?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=pedro-mendonca/Translation-Stats&amp;utm_campaign=Badge_Grade)";
						header += "\n";

						// Remove traditional WordPress plugin headers.
						readme = readme.replace( new RegExp( "(^\\*\\*.+:\\*\\*.+\\s*$\\n)", "gim" ), "newheader" );

						readme = readme.replace( new RegExp( "(newheader)", "gim" ), header );

						console.log( readme );

						return readme;
					}
				}
			},
		},

		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					exclude: [ '\.git/*', 'bin/*', 'node_modules/*', 'vendor/*', 'tests/*', 'wp-content/*' ],
					mainFile: 'translation-stats.php',
					potFilename: 'translation-stats.pot',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true
					},
					type: 'wp-plugin',
					updateTimestamp: true
				}
			}
		},
	} );

	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-wp-readme-to-markdown' );
	grunt.registerTask( 'default', [ 'i18n','readme' ] );
	grunt.registerTask( 'i18n', ['addtextdomain', 'makepot'] );
	grunt.registerTask( 'readme', ['wp_readme_to_markdown'] );

	grunt.util.linefeed = '\n';

};
