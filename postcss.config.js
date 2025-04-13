/* global module require */
module.exports = {
	plugins: [
		require( 'autoprefixer' )( {
			cascade: false
		} ),
		require( 'postcss-preset-env' ),
	],
};
