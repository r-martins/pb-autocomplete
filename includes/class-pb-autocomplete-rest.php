<?php
/**
 * REST API for PB Autocomplete settings (used by the block editor).
 * Registers post meta so the editor can mark the post dirty; on save we write to option instead of post meta.
 *
 * @package PB_Autocomplete
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class PB_Autocomplete_REST
 */
class PB_Autocomplete_REST {

	const NAMESPACE   = 'pb-autocomplete/v1';
	const OPTION_NAME = 'pb_autocomplete_settings';

	const META_BILLING  = '_pb_autocomplete_postcode_first_billing';
	const META_SHIPPING = '_pb_autocomplete_postcode_first_shipping';

	/**
	 * Init.
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_action( 'init', array( __CLASS__, 'register_post_meta' ) );
		add_filter( 'update_post_metadata', array( __CLASS__, 'intercept_meta_save' ), 10, 5 );
	}

	/**
	 * Register post meta so the block editor accepts it and sends it on save.
	 */
	public static function register_post_meta() {
		$opts = get_option( self::OPTION_NAME, array() );
		$opts = is_array( $opts ) ? $opts : array();
		$default_billing  = ! empty( $opts['postcode_first_billing'] );
		$default_shipping = ! empty( $opts['postcode_first_shipping'] );

		foreach ( array( 'post', 'page' ) as $post_type ) {
			register_post_meta(
				$post_type,
				self::META_BILLING,
				array(
					'type'          => 'boolean',
					'single'        => true,
					'show_in_rest'  => true,
					'default'       => $default_billing,
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
			register_post_meta(
				$post_type,
				self::META_SHIPPING,
				array(
					'type'          => 'boolean',
					'single'        => true,
					'show_in_rest'  => true,
					'default'       => $default_shipping,
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	/**
	 * When the editor saves the post with our meta, write to option and prevent saving to post meta.
	 *
	 * @param null|bool $check    Short-circuit.
	 * @param int      $object_id Post ID.
	 * @param string   $meta_key  Meta key.
	 * @param mixed    $meta_value Value.
	 * @param mixed    $prev_value Previous value.
	 * @return bool True to short-circuit (we handled it; do not write to post meta).
	 */
	public static function intercept_meta_save( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		if ( $meta_key !== self::META_BILLING && $meta_key !== self::META_SHIPPING ) {
			return $check;
		}
		$opts = get_option( self::OPTION_NAME, array() );
		$opts = is_array( $opts ) ? $opts : array();
		$opts[ $meta_key === self::META_BILLING ? 'postcode_first_billing' : 'postcode_first_shipping' ] = (bool) $meta_value;
		update_option( self::OPTION_NAME, $opts );
		return true;
	}

	/**
	 * Register REST routes.
	 */
	public static function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/settings',
			array(
				'methods'             => 'GET',
				'permission_callback' => array( __CLASS__, 'permission_check' ),
				'callback'            => array( __CLASS__, 'get_settings' ),
			)
		);
		register_rest_route(
			self::NAMESPACE,
			'/settings',
			array(
				'methods'             => 'POST',
				'permission_callback' => array( __CLASS__, 'permission_check' ),
				'callback'            => array( __CLASS__, 'update_settings' ),
				'args'                => array(
					'postcode_first_billing'  => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'postcode_first_shipping' => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
			)
		);
	}

	/**
	 * Permission callback.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool
	 */
	public static function permission_check( $request ) {
		return current_user_can( 'manage_woocommerce' );
	}

	/**
	 * GET settings.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public static function get_settings( $request ) {
		$opts = get_option( self::OPTION_NAME, array() );
		return new WP_REST_Response(
			array(
				'postcode_first_billing'  => ! empty( $opts['postcode_first_billing'] ),
				'postcode_first_shipping' => ! empty( $opts['postcode_first_shipping'] ),
			),
			200
		);
	}

	/**
	 * POST settings.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function update_settings( $request ) {
		$billing  = (bool) $request->get_param( 'postcode_first_billing' );
		$shipping = (bool) $request->get_param( 'postcode_first_shipping' );
		$opts     = get_option( self::OPTION_NAME, array() );
		$opts     = is_array( $opts ) ? $opts : array();
		update_option(
			self::OPTION_NAME,
			array_merge( $opts, array(
				'postcode_first_billing'  => $billing,
				'postcode_first_shipping' => $shipping,
			) )
		);
		return new WP_REST_Response( array( 'success' => true ), 200 );
	}
}
