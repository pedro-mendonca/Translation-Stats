/**
 * External dependencies
 */
const wordpress = require( '@wordpress/eslint-plugin' );

module.exports = [
	{
		ignores: [ 'assets/build/', 'assets/lib/', 'vendor/', '*.js' ],
	},
	...wordpress.configs[ 'recommended-with-formatting' ],
	{
		settings: {
			// Provided by WordPress, not installed with npm.
			'import/core-modules': [ 'jquery' ],
		},
		rules: {
			'no-console': 'off',
		},
	},
];
