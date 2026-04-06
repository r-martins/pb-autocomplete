<?php
/**
 * Plugin Name:       PB Autocomplete CEP for WooCommerce
 * Plugin URI:        https://pbintegracoes.com/
 * Description:       Autocompleta endereço a partir do CEP no Checkout em Blocos. Requer PagBank Connect ativo com ao menos um método de pagamento disponível.
 * Version:           1.0.6
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

define( 'PB_AUTOCOMPLETE_VERSION', '1.0.6' );
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
	// Defer gateway check to init: iterating gateways in plugins_loaded triggers other plugins' i18n too early (WP 6.7+).
	add_action( 'init', 'pb_autocomplete_register_payment_method_notice', 1 );
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

/**
 * Dependency notices: only on plugin management screens (Guideline 11 — do not hijack the admin).
 *
 * @return bool
 */
function pb_autocomplete_should_show_dependency_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return false;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->id, array( 'plugins', 'plugin-install' ), true ) ) {
		return false;
	}
	return true;
}

function pb_autocomplete_woo_missing_notice() {
	if ( ! pb_autocomplete_should_show_dependency_notice() ) {
		return;
	}
	echo '<div class="notice notice-error"><p>' . esc_html__( 'PB Autocomplete requer o WooCommerce instalado e ativo.', 'pb-autocomplete' ) . '</p></div>';
}

function pb_autocomplete_pagbank_missing_notice() {
	if ( ! pb_autocomplete_should_show_dependency_notice() ) {
		return;
	}
	echo '<div class="notice notice-error"><p>' . esc_html__( 'PB Autocomplete requer o plugin PagBank Connect instalado e ativo.', 'pb-autocomplete' ) . '</p></div>';
}

/**
 * Register admin notice after init when no PagBank payment method is enabled.
 */
function pb_autocomplete_register_payment_method_notice() {
	if ( ! is_admin() || ! class_exists( 'PB_Autocomplete_Checkout', false ) ) {
		return;
	}
	if ( ! PB_Autocomplete_Checkout::has_pagbank_payment_enabled() ) {
		add_action( 'admin_notices', 'pb_autocomplete_pagbank_no_payment_method_notice' );
	}
}

/**
 * PagBank Connect is active but no PagBank payment method is enabled (PIX, cartão, boleto, Checkout PagBank, recorrência, etc.).
 */
function pb_autocomplete_should_show_payment_method_notice() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return false;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen ) {
		return false;
	}
	if ( in_array( $screen->id, array( 'plugins', 'plugin-install' ), true ) ) {
		return true;
	}
	if ( 'woocommerce_page_wc-settings' === $screen->id && isset( $_GET['tab'] ) && 'checkout' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return true;
	}
	return false;
}

function pb_autocomplete_pagbank_no_payment_method_notice() {
	if ( ! pb_autocomplete_should_show_payment_method_notice() ) {
		return;
	}
	$payments_url = admin_url( 'admin.php?page=wc-settings&tab=checkout' );
	$message      = sprintf(
		/* translators: %s: link to WooCommerce checkout (payment methods) settings */
		__( 'PB Autocomplete exige ao menos um método de pagamento PagBank ativo (PIX, Cartão de Crédito, Boleto, Checkout PagBank ou Recorrência). Ative um deles em %s.', 'pb-autocomplete' ),
		'<a href="' . esc_url( $payments_url ) . '">' . esc_html__( 'WooCommerce → Configurações → Finalizar compra', 'pb-autocomplete' ) . '</a>'
	);
	echo '<div class="notice notice-warning"><p>' . wp_kses_post( $message ) . '</p></div>';
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
