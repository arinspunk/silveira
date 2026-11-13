<?php
/**
 * ACF integration (optional): field access, ACF Local JSON paths and an admin notice if it is missing.
 *
 * @package Silveira
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is ACF active?
 *
 * @return bool
 */
function silveira_has_acf() {
	return function_exists( 'get_field' );
}

/**
 * Value of an ACF field, or null if ACF is not active.
 *
 * The only place in the theme that calls get_field(): without ACF, templates skip that data instead of a fatal error.
 *
 * @param string    $name    Field name.
 * @param int|false $post_id Post ID (false: the current post).
 * @return mixed
 */
function silveira_field( $name, $post_id = false ) {
	if ( ! silveira_has_acf() ) {
		return null;
	}
	return get_field( $name, $post_id );
}

// Plugins load before the theme: by now we know whether ACF is active.
if ( silveira_has_acf() ) {
	add_filter( 'acf/settings/save_json', 'silveira_acf_json_save_point' );
	add_filter( 'acf/settings/load_json', 'silveira_acf_json_load_point' );
} else {
	add_action( 'admin_notices', 'silveira_acf_missing_notice' );
}

/**
 * Admin notice, only for users who can activate plugins, when ACF is not active.
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
 * @param string $path Default path (ignored).
 * @return string
 */
function silveira_acf_json_save_point( $path ) {
	unset( $path );
	return get_theme_file_path( 'acf-json' );
}

/**
 * Filter: ACF Local JSON load path.
 *
 * @param string[] $paths Load paths.
 * @return string[]
 */
function silveira_acf_json_load_point( $paths ) {
	unset( $paths[0] );
	$paths[] = get_theme_file_path( 'acf-json' );
	return $paths;
}
