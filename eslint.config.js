/**
 * External dependencies
 */
const wordpress = require( '@wordpress/eslint-plugin' );

module.exports = [
	{
		ignores: [ '**/*.min.js', 'assets/lib/', 'vendor/', '*.js' ],
	},
	...wordpress.configs.es5,
	{
		languageOptions: {
			globals: {
				jQuery: 'readonly',
				console: 'readonly',
			},
		},
		rules: {
			'no-console': 'off',
		},
	},
];
