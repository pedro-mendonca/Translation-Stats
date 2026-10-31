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

module.exports = {
	...defaultConfig,
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
