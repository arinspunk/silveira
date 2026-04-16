<?php
/**
 * Funciones del theme silveira.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_theme_support( 'title-tag' );

/**
 * ¿Cargar assets desde el servidor de desarrollo de Vite (HMR)?
 *
 * Se controla con la variable de entorno SILVEIRA_VITE_DEV (p. ej. en docker-compose / .env).
 * No usar WP_DEBUG para esto: son preocupaciones distintas.
 *
 * @return bool
 */
function silveira_is_vite_dev() {
	$raw = getenv( 'SILVEIRA_VITE_DEV' );
	if ( false === $raw ) {
		return false;
	}
	$raw = strtolower( trim( (string) $raw ) );
	return in_array( $raw, array( '1', 'true', 'yes', 'on' ), true );
}

/**
 * URL base del servidor Vite (desde PHP dentro de Docker: host del equipo = host.docker.internal en Docker Desktop).
 *
 * @return string Sin barra final.
 */
function silveira_vite_dev_server_url() {
	$url = getenv( 'VITE_DEV_SERVER_URL' );
	if ( is_string( $url ) && '' !== trim( $url ) ) {
		return untrailingslashit( trim( $url ) );
	}
	return 'http://host.docker.internal:5173';
}
