<?php
/**
 * Integración con ACF (opcional): acceso a campos, rutas de ACF Local JSON y aviso en el admin si falta.
 *
 * @package Silveira
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ¿Está ACF activo?
 *
 * @return bool
 */
function silveira_has_acf() {
	return function_exists( 'get_field' );
}

/**
 * Valor de un campo de ACF, o null si ACF no está activo.
 *
 * Único punto del theme que llama a get_field(): así, sin ACF, las plantillas omiten esos datos en vez de dar un error fatal.
 *
 * @param string    $name    Nombre del campo.
 * @param int|false $post_id ID del post (false: el post actual).
 * @return mixed
 */
function silveira_field( $name, $post_id = false ) {
	if ( ! silveira_has_acf() ) {
		return null;
	}
	return get_field( $name, $post_id );
}

// Los plugins se cargan antes que el theme: aquí ya se sabe si ACF está activo.
if ( silveira_has_acf() ) {
	add_filter( 'acf/settings/save_json', 'silveira_acf_json_save_point' );
	add_filter( 'acf/settings/load_json', 'silveira_acf_json_load_point' );
} else {
	add_action( 'admin_notices', 'silveira_acf_missing_notice' );
}

/**
 * Aviso en el admin, solo para quien puede activar plugins, cuando ACF no está activo.
 */
function silveira_acf_missing_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__( 'O tema silveira precisa do plugin Advanced Custom Fields (ACF). Sem ele, os campos dos projetos (lema, endereço e coordenadas) nom estám disponíveis e o mapa da portada aparece sem pontos.', 'silveira' )
	);
}

/**
 * Filter: ACF Local JSON save path.
 *
 * @param string $path Ruta por defecto (se ignora).
 * @return string
 */
function silveira_acf_json_save_point( $path ) {
	unset( $path );
	return get_theme_file_path( 'acf-json' );
}

/**
 * Filter: ACF Local JSON load path.
 *
 * @param string[] $paths Rutas de carga.
 * @return string[]
 */
function silveira_acf_json_load_point( $paths ) {
	unset( $paths[0] );
	$paths[] = get_theme_file_path( 'acf-json' );
	return $paths;
}
