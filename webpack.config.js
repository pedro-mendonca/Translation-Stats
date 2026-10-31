/**
 * External dependencies
 */
const path = require( 'path' );

/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

/**
 * Remove the empty scripts emitted for the style only entries, keeping their asset files.
 */
class RemoveEmptyStyleScripts {
	apply( compiler ) {
		compiler.hooks.thisCompilation.tap( 'RemoveEmptyStyleScripts', ( compilation ) => {
			compilation.hooks.processAssets.tap(
				{
					name: 'RemoveEmptyStyleScripts',
					stage: compiler.webpack.Compilation.PROCESS_ASSETS_STAGE_REPORT,
				},
				( assets ) => {
					Object.keys( assets )
						.filter( ( name ) => name.startsWith( 'css/' ) && name.endsWith( '.js' ) && '' === assets[ name ].source().toString().trim() )
						.forEach( ( name ) => compilation.deleteAsset( name ) );
				}
			);
		} );
	}
}

/**
 * Disable the PostCSS config files lookup, to only use the @wordpress/scripts default config.
 * Otherwise, a config file in a parent directory, like a development environment, changes the build.
 *
 * @param {Array} rules Webpack module rules.
 *
 * @return {Array} Webpack module rules.
 */
function disablePostCSSConfigLookup( rules ) {
	rules
		.filter( ( rule ) => Array.isArray( rule.use ) )
		.flatMap( ( rule ) => rule.use )
		.filter( ( loader ) => loader.loader && loader.loader.includes( 'postcss-loader' ) )
		.forEach( ( loader ) => {
			loader.options.postcssOptions = Object.assign( {}, loader.options.postcssOptions, { config: false } );
		} );

	return rules;
}

module.exports = {
	...defaultConfig,
	module: {
		...defaultConfig.module,
		rules: disablePostCSSConfigLookup( defaultConfig.module.rules ),
	},
	plugins: [ ...defaultConfig.plugins, new RemoveEmptyStyleScripts() ],
	entry: {
		'js/admin-plugins': './assets/src/js/admin-plugins.js',
		'js/admin-settings': './assets/src/js/admin-settings.js',
		'css/admin': './assets/src/css/admin.scss',
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'assets/build' ),
	},
};
