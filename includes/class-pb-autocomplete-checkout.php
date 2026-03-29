<?php
/**
 * Checkout Block integration: enqueue script, REST endpoint, and CEP-first field order.
 *
 * @package PB_Autocomplete
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PB_Autocomplete_Checkout
 */
class PB_Autocomplete_Checkout {

	/**
	 * Init hooks.
	 */
	public static function init() {
		add_action( 'woocommerce_blocks_checkout_enqueue_data', array( __CLASS__, 'enqueue_checkout_data' ), 15 );
		add_filter( 'woocommerce_blocks_register_script_dependencies', array( __CLASS__, 'add_script_dependencies' ), 10, 2 );
		add_filter( 'woocommerce_shared_settings', array( __CLASS__, 'filter_default_fields_postcode_first' ), 10, 1 );
	}

	/**
	 * Whether a WooCommerce payment gateway ID belongs to PagBank Connect (PIX, cartão, boleto,
	 * Checkout PagBank, recorrência, ou gateway unificado rm-pagbank).
	 *
	 * @param string $gateway_id Gateway id.
	 * @return bool
	 */
	public static function is_pagbank_gateway_id( $gateway_id ) {
		if ( ! is_string( $gateway_id ) || '' === $gateway_id ) {
			return false;
		}
		if ( 'rm-pagbank' === $gateway_id ) {
			return true;
		}
		return strpos( $gateway_id, 'rm-pagbank-' ) === 0;
	}

	/**
	 * True if at least one PagBank gateway is enabled in WooCommerce (admin-safe; does not rely on checkout session).
	 *
	 * @return bool
	 */
	public static function has_pagbank_payment_enabled() {
		if ( ! function_exists( 'WC' ) || ! WC()->payment_gateways || empty( WC()->payment_gateways->payment_gateways ) ) {
			return false;
		}
		foreach ( WC()->payment_gateways->payment_gateways as $gateway ) {
			if ( ! is_object( $gateway ) || ! isset( $gateway->enabled, $gateway->id ) ) {
				continue;
			}
			if ( 'yes' !== $gateway->enabled ) {
				continue;
			}
			if ( self::is_pagbank_gateway_id( $gateway->id ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if PagBank has at least one available payment method on checkout.
	 *
	 * @return bool
	 */
	public static function has_pagbank_available() {
		if ( ! function_exists( 'WC' ) || ! WC()->payment_gateways ) {
			return false;
		}
		$available = WC()->payment_gateways->get_available_payment_gateways();
		foreach ( array_keys( $available ) as $id ) {
			if ( self::is_pagbank_gateway_id( $id ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Add our script and data when checkout block enqueues data. Only when PagBank has a method available.
	 */
	public static function enqueue_checkout_data() {
		if ( ! self::has_pagbank_available() ) {
			return;
		}
		self::register_script();
		$asset_registry = null;
		if ( class_exists( '\Automattic\WooCommerce\Blocks\Package' ) && class_exists( '\Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry' ) ) {
			try {
				$asset_registry = \Automattic\WooCommerce\Blocks\Package::container()->get( \Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class );
			} catch ( Exception $e ) {
				$asset_registry = null;
			}
		}
		if ( $asset_registry && ! $asset_registry->exists( 'pbAutocomplete' ) ) {
			$options = get_option( 'pb_autocomplete_settings', array() );
			$asset_registry->add( 'pbAutocomplete', array(
				'postcodeFirstBilling'  => ! empty( $options['postcode_first_billing'] ),
				'postcodeFirstShipping' => ! empty( $options['postcode_first_shipping'] ),
			) );
		}
	}

	/**
	 * Register our script (built React/Blocks extension; used as dependency of checkout block).
	 */
	private static function register_script() {
		if ( wp_script_is( 'pb-autocomplete-checkout-blocks', 'registered' ) ) {
			return;
		}
		$script_path = 'build/js/frontend/checkout-autocomplete.js';
		$asset_path = PB_AUTOCOMPLETE_PATH . 'build/js/frontend/checkout-autocomplete.asset.php';
		$script_url = PB_AUTOCOMPLETE_URL . $script_path;
		$deps       = array( 'wc-blocks-checkout', 'wp-plugins', 'wp-data', 'wc-settings' );
		$version    = PB_AUTOCOMPLETE_VERSION;
		if ( is_readable( $asset_path ) ) {
			$asset   = include $asset_path;
			$deps    = isset( $asset['dependencies'] ) ? array_merge( $asset['dependencies'], array( 'wc-blocks-checkout' ) ) : $deps;
			$version = isset( $asset['version'] ) ? $asset['version'] : $version;
		}
		wp_register_script(
			'pb-autocomplete-checkout-blocks',
			$script_url,
			array_unique( $deps ),
			$version,
			true
		);
	}

	/**
	 * Coloca o CEP acima dos campos de endereço quando a opção estiver ativa (index antes de address_1).
	 * Altera defaultFields e também countryData[].locale[].postcode para que o merge por país não sobrescreva.
	 *
	 * @param array $settings Dados do asset registry (woocommerce_shared_settings).
	 * @return array
	 */
	public static function filter_default_fields_postcode_first( $settings ) {
		if ( ! is_array( $settings ) || empty( $settings['defaultFields'] ) || empty( $settings['defaultFields']['postcode'] ) ) {
			return $settings;
		}
		$options = get_option( 'pb_autocomplete_settings', array() );
		$first_billing  = ! empty( $options['postcode_first_billing'] );
		$first_shipping = ! empty( $options['postcode_first_shipping'] );
		if ( ! $first_billing && ! $first_shipping ) {
			return $settings;
		}
		$postcode_index = 35; // Antes de address_1 (40).
		$settings['defaultFields']['postcode'] = array_merge(
			$settings['defaultFields']['postcode'],
			array( 'index' => $postcode_index )
		);
		// O frontend faz merge de defaultFields com countryData[country].locale; o locale sobrescreve o index.
		if ( ! empty( $settings['countryData'] ) && is_array( $settings['countryData'] ) ) {
			foreach ( $settings['countryData'] as $country_code => $data ) {
				if ( ! is_array( $data ) || empty( $data['locale'] ) || ! isset( $data['locale']['postcode'] ) ) {
					continue;
				}
				$settings['countryData'][ $country_code ]['locale']['postcode'] = array_merge(
					$data['locale']['postcode'],
					array( 'index' => $postcode_index )
				);
			}
		}
		return $settings;
	}

	/**
	 * Add our script as dependency of checkout block so it runs first and can reorder fields.
	 */
	public static function add_script_dependencies( $dependencies, $handle ) {
		if ( ! in_array( $handle, array( 'wc-checkout-block', 'wc-checkout-block-frontend' ), true ) ) {
			return $dependencies;
		}
		if ( ! self::has_pagbank_available() ) {
			return $dependencies;
		}
		self::register_script();
		$dependencies[] = 'pb-autocomplete-checkout-blocks';
		return $dependencies;
	}
}
