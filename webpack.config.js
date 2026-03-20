const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const WooCommerceDependencyExtractionWebpackPlugin = require( '@woocommerce/dependency-extraction-webpack-plugin' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		'frontend/checkout-autocomplete': path.resolve( __dirname, 'src', 'checkout-autocomplete.js' ),
		'editor/checkout-address-settings': path.resolve( __dirname, 'src', 'editor-checkout-address-settings.js' ),
	},
	output: {
		path: path.resolve( __dirname, 'build', 'js' ),
		filename: '[name].js',
	},
	plugins: [
		...defaultConfig.plugins.filter(
			( plugin ) => plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new WooCommerceDependencyExtractionWebpackPlugin(),
	],
};
