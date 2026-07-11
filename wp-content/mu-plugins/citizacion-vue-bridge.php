<?php
/**
 * Plugin Name: Citización Vue Bridge
 * Description: Autentica usuarios de la app Vue en WooCommerce mediante JWT y sincroniza el carrito híbrido.
 * Version: 1.0.0
 * Author: Citización Ecommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CITIZACION_VUE_JWT_PARAM', 'vue_jwt' );
define( 'CITIZACION_CART_LOAD_PARAM', 'cargar-carrito' );

/**
 * Punto de entrada del puente híbrido Vue ↔ WordPress.
 */
final class Citizacion_Vue_Bridge {

	/**
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * @return self
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'template_redirect', array( $this, 'handle_hybrid_checkout' ), 5 );
	}

	/**
	 * Procesa autenticación JWT y carga de carrito enviados desde Vue.
	 *
	 * @return void
	 */
	public function handle_hybrid_checkout() {
		$jwt_token = $this->get_query_param( CITIZACION_VUE_JWT_PARAM );
		$cart_data = $this->get_query_param( CITIZACION_CART_LOAD_PARAM );

		if ( '' === $jwt_token && '' === $cart_data ) {
			return;
		}

		if ( '' !== $jwt_token ) {
			$this->authenticate_from_vue_jwt( $jwt_token );
		}

		if ( '' !== $cart_data ) {
			$this->load_cart_from_vue_payload( $cart_data );
		}

		$this->redirect_to_clean_checkout();
	}

	/**
	 * @param string $param_name Nombre del parámetro GET.
	 * @return string
	 */
	private function get_query_param( $param_name ) {
		if ( ! isset( $_GET[ $param_name ] ) ) {
			return '';
		}

		$value = wp_unslash( $_GET[ $param_name ] );

		if ( ! is_string( $value ) || '' === trim( $value ) ) {
			return '';
		}

		return sanitize_text_field( $value );
	}

	/**
	 * Valida el JWT emitido por WPGraphQL JWT Authentication e inicia sesión en WordPress/WooCommerce.
	 *
	 * @param string $jwt Token JWT recibido desde Vue.
	 * @return void
	 */
	private function authenticate_from_vue_jwt( $jwt ) {
		if ( is_user_logged_in() ) {
			return;
		}

		if ( ! class_exists( '\WPGraphQL\JWT_Authentication\Auth' ) ) {
			error_log( '[Citizacion Vue Bridge] WPGraphQL JWT Authentication no está disponible.' );
			return;
		}

		$validated = \WPGraphQL\JWT_Authentication\Auth::validate_token( $jwt );

		if ( is_wp_error( $validated ) || empty( $validated->data->user->id ) ) {
			$message = is_wp_error( $validated ) ? $validated->get_error_message() : 'Token inválido.';
			error_log( '[Citizacion Vue Bridge] Error al validar JWT: ' . $message );
			return;
		}

		$user_id = (int) $validated->data->user->id;
		$user    = get_user_by( 'id', $user_id );

		if ( ! $user instanceof WP_User ) {
			error_log( '[Citizacion Vue Bridge] No se encontró usuario para el JWT recibido.' );
			return;
		}

		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );
		do_action( 'wp_login', $user->user_login, $user );
		$this->bootstrap_woocommerce_customer( $user_id );

		error_log(
			sprintf(
				'[Citizacion Vue Bridge] Sesión iniciada para el usuario ID %d en checkout híbrido.',
				$user_id
			)
		);
	}

	/**
	 * @param int $user_id ID del usuario autenticado.
	 * @return void
	 */
	private function bootstrap_woocommerce_customer( $user_id ) {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		if ( null === WC()->session ) {
			WC()->initialize_session();
		}

		WC()->customer = new WC_Customer( $user_id, true );

		if ( function_exists( 'wc_set_customer_auth_cookie' ) ) {
			wc_set_customer_auth_cookie( $user_id );
		}
	}

	/**
	 * Reemplaza el carrito de WooCommerce con los ítems enviados desde Vue.
	 *
	 * @param string $cart_data Formato id:cantidad,id:cantidad.
	 * @return void
	 */
	private function load_cart_from_vue_payload( $cart_data ) {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		WC()->cart->empty_cart();

		$items = explode( ',', $cart_data );

		foreach ( $items as $item ) {
			$pair = explode( ':', $item );

			if ( count( $pair ) < 2 ) {
				continue;
			}

			$product_id = (int) $pair[0];
			$quantity   = max( 1, (int) $pair[1] );

			if ( $product_id > 0 ) {
				WC()->cart->add_to_cart( $product_id, $quantity );
			}
		}

		error_log( '[Citizacion Vue Bridge] Carrito sincronizado desde Vue en checkout.' );
	}

	/**
	 * Elimina parámetros sensibles de la URL y redirige al checkout limpio.
	 *
	 * @return void
	 */
	private function redirect_to_clean_checkout() {
		$redirect_url = function_exists( 'wc_get_checkout_url' )
			? wc_get_checkout_url()
			: home_url( '/' );

		wp_safe_redirect( $redirect_url );
		exit;
	}
}

Citizacion_Vue_Bridge::instance();
