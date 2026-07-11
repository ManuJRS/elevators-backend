<?php
/**
 * Plugin Name: Citización JWT Config
 * Description: Define la clave secreta de WPGraphQL JWT Authentication para el entorno Docker/local.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'GRAPHQL_JWT_AUTH_SECRET_KEY' ) ) {
	$secret = getenv( 'GRAPHQL_JWT_AUTH_SECRET_KEY' );

	if ( empty( $secret ) ) {
		$secret = 'citizacion-local-jwt-secret-change-me';
	}

	define( 'GRAPHQL_JWT_AUTH_SECRET_KEY', $secret );
}

add_filter(
	'graphql_jwt_auth_expire',
	static function () {
		return DAY_IN_SECONDS;
	}
);
