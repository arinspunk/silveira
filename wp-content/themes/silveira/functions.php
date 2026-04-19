<?php
/**
 * Funciones del theme silveira.
 *
 * @package Silveira
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Clave de entrada en manifest.json (Vite) y ruta lógica en modo dev. */
define( 'SILVEIRA_VITE_ENTRY', 'src/main.js' );

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
 * URL base del servidor Vite (la usa el navegador en <script src>; misma máquina que el dev → 127.0.0.1).
 *
 * @return string Sin barra final.
 */
function silveira_vite_dev_server_url() {
	$url = getenv( 'VITE_DEV_SERVER_URL' );
	if ( is_string( $url ) && '' !== trim( $url ) ) {
		return untrailingslashit( trim( $url ) );
	}
	return 'http://127.0.0.1:5173';
}

/**
 * Ruta absoluta al manifest de Vite.
 *
 * @return string
 */
function silveira_vite_manifest_path() {
	return trailingslashit( get_stylesheet_directory() ) . 'assets/.vite/manifest.json';
}

/**
 * Manifest decodificado o false si falta o es inválido.
 *
 * @return array<string, mixed>|false
 */
function silveira_vite_get_manifest() {
	static $manifest = null;
	if ( null !== $manifest ) {
		return $manifest;
	}
	$path = silveira_vite_manifest_path();
	if ( ! is_readable( $path ) ) {
		$manifest = false;
		return $manifest;
	}
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- fichero local del theme.
	$json     = file_get_contents( $path );
	$data     = json_decode( $json, true );
	$manifest = is_array( $data ) ? $data : false;
	return $manifest;
}

/**
 * URL pública a un fichero bajo assets/ del theme.
 *
 * @param string $relative Ruta relativa dentro de assets/ (p. ej. js/main-xxx.js).
 * @return string
 */
function silveira_vite_asset_url( $relative ) {
	return trailingslashit( get_stylesheet_directory_uri() ) . 'assets/' . ltrim( $relative, '/' );
}

/**
 * Ruta del sistema a un fichero bajo assets/ del theme.
 *
 * @param string $relative Ruta relativa dentro de assets/.
 * @return string
 */
function silveira_vite_asset_path( $relative ) {
	return trailingslashit( get_stylesheet_directory() ) . 'assets/' . ltrim( $relative, '/' );
}

/**
 * Base de URL del theme en el servidor de Vite (debe coincidir con vite.config base).
 *
 * @return string Con barra inicial, sin barra final.
 */
function silveira_vite_theme_base_path() {
	return '/wp-content/themes/' . get_stylesheet();
}

/**
 * Scripts que deben cargarse como ES modules.
 *
 * @return string[]
 */
function silveira_vite_module_script_handles() {
	return array( 'silveira-vite-client', 'silveira-main' );
}

add_action( 'wp_enqueue_scripts', 'silveira_enqueue_vite_assets', 20 );

/**
 * Encola CSS/JS del theme: Vite dev o build según entorno.
 */
function silveira_enqueue_vite_assets() {
	if ( silveira_is_vite_dev() ) {
		silveira_enqueue_vite_dev();
		return;
	}
	silveira_enqueue_vite_prod();
}

/**
 * Modo desarrollo: @vite/client + entrada (HMR).
 */
function silveira_enqueue_vite_dev() {
	$origin = silveira_vite_dev_server_url();
	$base   = silveira_vite_theme_base_path();
	$client = $origin . $base . '/@vite/client';
	$entry  = $origin . $base . '/' . SILVEIRA_VITE_ENTRY;
	$ver    = wp_get_theme()->get( 'Version' );

	wp_enqueue_script( 'silveira-vite-client', $client, array(), $ver, true );
	wp_enqueue_script( 'silveira-main', $entry, array( 'silveira-vite-client' ), $ver, true );
}

/**
 * Modo producción: manifest + filemtime.
 */
function silveira_enqueue_vite_prod() {
	$manifest = silveira_vite_get_manifest();
	$key      = SILVEIRA_VITE_ENTRY;
	if ( ! is_array( $manifest ) || empty( $manifest[ $key ] ) || ! is_array( $manifest[ $key ] ) ) {
		return;
	}

	$entry         = $manifest[ $key ];
	$manifest_path = silveira_vite_manifest_path();
	$mtime         = is_readable( $manifest_path ) ? filemtime( $manifest_path ) : false;
	$ver           = false !== $mtime ? (string) $mtime : wp_get_theme()->get( 'Version' );

	if ( ! empty( $entry['css'] ) && is_array( $entry['css'] ) ) {
		foreach ( $entry['css'] as $i => $css_rel ) {
			$css_rel = (string) $css_rel;
			$handle  = ( 0 === (int) $i ) ? 'silveira-main' : 'silveira-main-' . (int) $i;
			$path    = silveira_vite_asset_path( $css_rel );
			$css_ver = is_readable( $path ) ? (string) filemtime( $path ) : $ver;
			wp_enqueue_style(
				$handle,
				silveira_vite_asset_url( $css_rel ),
				array(),
				$css_ver
			);
		}
	}

	if ( ! empty( $entry['file'] ) ) {
		$js_rel = (string) $entry['file'];
		$path   = silveira_vite_asset_path( $js_rel );
		$js_ver = is_readable( $path ) ? (string) filemtime( $path ) : $ver;
		wp_enqueue_script(
			'silveira-main',
			silveira_vite_asset_url( $js_rel ),
			array(),
			$js_ver,
			true
		);
	}
}

add_filter( 'script_loader_tag', 'silveira_vite_script_module_type', 10, 3 );

/**
 * Marca scripts del theme como type="module" (Vite).
 *
 * @param string $tag    HTML del script.
 * @param string $handle Handle registrado.
 * @param string $src    URL del script.
 * @return string
 */
function silveira_vite_script_module_type( $tag, $handle, $src ) {
	unset( $src );
	if ( ! in_array( $handle, silveira_vite_module_script_handles(), true ) ) {
		return $tag;
	}
	// WordPress defaults to type="text/javascript"; Vite entries must load as ES modules.
	if ( preg_match( '/type\s*=\s*([\'"])module\1/i', $tag ) ) {
		return $tag;
	}
	$tag = preg_replace( '/\stype=([\'"])text\/javascript\1/i', ' type=$1module$1', $tag, 1 );
	if ( ! preg_match( '/\stype\s*=/i', $tag ) ) {
		return str_replace( '<script ', '<script type="module" ', $tag );
	}
	return $tag;
}

/**
 * Include Custom Post Types and Taxonomies
 */
require_once get_template_directory() . '/inc/tax-shared.php';
require_once get_template_directory() . '/inc/cpt-projetos.php';
require_once get_template_directory() . '/inc/cpt-eventos.php';
require_once get_template_directory() . '/inc/cpt-recursos.php';
require_once get_template_directory() . '/inc/assets.php';

/**
 * Flush rewrite rules on theme switch/activation (Temporary logic for dev)
 */
add_action( 'after_switch_theme', 'silveira_rewrite_flush' );
function silveira_rewrite_flush() {
	silveira_register_shared_taxonomies();
	silveira_register_cpt_projeto();
	silveira_register_cpt_evento();
	silveira_register_cpt_recurso();
	flush_rewrite_rules();
}
