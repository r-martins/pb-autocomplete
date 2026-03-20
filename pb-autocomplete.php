<?php
/**
 * Plugin Name:       PB Autocomplete CEP for WooCommerce
 * Plugin URI:        https://pbintegracoes.com/
 * Description:       Autocompleta endereço a partir do CEP no Checkout em Blocos. Requer PagBank Connect ativo com ao menos um método de pagamento disponível.
 * Version:           1.0.3
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Ricardo Martins
 * License:           GPL-3.0
 * License URI:       https://opensource.org/license/gpl-3-0
 * Requires Plugins:  woocommerce, pagbank-connect
 * Text Domain:       pb-autocomplete
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'PB_AUTOCOMPLETE_VERSION', '1.0.3' );
define( 'PB_AUTOCOMPLETE_FILE', __FILE__ );
define( 'PB_AUTOCOMPLETE_PATH', plugin_dir_path( __FILE__ ) );
define( 'PB_AUTOCOMPLETE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check dependencies on load.
 */
add_action( 'plugins_loaded', 'pb_autocomplete_init', 20 );

function pb_autocomplete_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'pb_autocomplete_woo_missing_notice' );
		return;
	}
	if ( ! defined( 'WC_PAGSEGURO_CONNECT_VERSION' ) && ! class_exists( 'RM_PagBank\Connect' ) ) {
		add_action( 'admin_notices', 'pb_autocomplete_pagbank_missing_notice' );
		return;
	}
	require_once PB_AUTOCOMPLETE_PATH . 'includes/class-pb-autocomplete-checkout.php';
	require_once PB_AUTOCOMPLETE_PATH . 'includes/class-pb-autocomplete-rest.php';
	PB_Autocomplete_Checkout::init();
	PB_Autocomplete_REST::init();
	if ( is_admin() ) {
		add_action( 'enqueue_block_editor_assets', 'pb_autocomplete_enqueue_editor_script' );
	}
}

/**
 * Enfileira o script do editor para a opção "CEP acima" no bloco de endereço.
 */
function pb_autocomplete_enqueue_editor_script() {
	$script_path = 'build/js/editor/checkout-address-settings.js';
	$asset_path  = PB_AUTOCOMPLETE_PATH . 'build/js/editor/checkout-address-settings.asset.php';
	$script_url  = PB_AUTOCOMPLETE_URL . $script_path;
	$deps        = array( 'wp-element', 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-api-fetch', 'wp-i18n' );
	$version     = PB_AUTOCOMPLETE_VERSION;
	if ( is_readable( $asset_path ) ) {
		$asset   = include $asset_path;
		$deps    = isset( $asset['dependencies'] ) ? $asset['dependencies'] : $deps;
		$version = isset( $asset['version'] ) ? $asset['version'] : $version;
	}
	wp_enqueue_script(
		'pb-autocomplete-editor-address-settings',
		$script_url,
		$deps,
		$version,
		true
	);
}

function pb_autocomplete_woo_missing_notice() {
	echo '<div class="notice notice-error"><p>' . esc_html__( 'PB Autocomplete requer o WooCommerce instalado e ativo.', 'pb-autocomplete' ) . '</p></div>';
}

function pb_autocomplete_pagbank_missing_notice() {
	echo '<div class="notice notice-error"><p>' . esc_html__( 'PB Autocomplete requer o plugin PagBank Connect instalado e ativo.', 'pb-autocomplete' ) . '</p></div>';
}

/**
 * Deactivate self if PagBank Connect is deactivated.
 */
register_activation_hook( __FILE__, 'pb_autocomplete_activation' );

function pb_autocomplete_activation() {
	if ( ! class_exists( 'WooCommerce' ) || ( ! defined( 'WC_PAGSEGURO_CONNECT_VERSION' ) && ! class_exists( 'RM_PagBank\Connect' ) ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'PB Autocomplete requer WooCommerce e PagBank Connect instalados e ativos.', 'pb-autocomplete' ),
			'',
			[ 'back_link' => true ]
		);
	}
}
