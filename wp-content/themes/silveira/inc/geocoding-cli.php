<?php
/**
 * Comando WP-CLI `wp silveira geocode`: geocodifica en lote los proyectos sin coordenadas.
 *
 * Solo se carga con WP-CLI (ver functions.php).
 *
 * @package Silveira
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/** Separación mínima entre peticiones a Nominatim (política de uso: 1 por segundo). */
const SILVEIRA_GEOCODE_CLI_INTERVAL = 1.0;

/**
 * Geocodifica los proyectos que tienen dirección y no tienen coordenadas.
 *
 * Hace como máximo una petición por segundo a Nominatim. Las coordenadas existentes nunca se tocan.
 *
 * ## OPTIONS
 *
 * [--dry-run]
 * : Lista los proyectos que se geocodificarían, sin hacer peticiones ni guardar nada.
 *
 * ## EXAMPLES
 *
 *     wp silveira geocode --dry-run
 *     wp silveira geocode
 *
 * @param string[]             $args       Argumentos posicionales (no se usan).
 * @param array<string, mixed> $assoc_args Opciones.
 */
function silveira_geocode_cli( $args, $assoc_args ) {
	if ( ! silveira_has_acf() ) {
		WP_CLI::error( __( 'O plugin ACF nom está ativo.', 'silveira' ) );
	}
	$dry_run = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false );

	$ids     = get_posts(
		array(
			'post_type'   => 'projeto',
			'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'numberposts' => -1,
			'fields'      => 'ids',
			'orderby'     => 'ID',
			'order'       => 'ASC',
		)
	);
	$pending = array();
	foreach ( $ids as $id ) {
		$address = silveira_geocode_normalize_address( (string) silveira_field( 'projeto_endereco', $id ) );
		if ( '' === $address ) {
			continue;
		}
		if ( is_numeric( silveira_field( 'projeto_latitude', $id ) ) && is_numeric( silveira_field( 'projeto_longitude', $id ) ) ) {
			continue;
		}
		$pending[ $id ] = $address;
	}

	if ( ! $pending ) {
		WP_CLI::success( __( 'Nom há projetos com endereço e sem coordenadas.', 'silveira' ) );
		return;
	}

	$rows = array();
	if ( $dry_run ) {
		foreach ( $pending as $id => $address ) {
			$rows[] = array(
				'id'       => $id,
				'projeto'  => get_the_title( $id ),
				'endereco' => $address,
			);
		}
		\WP_CLI\Utils\format_items( 'table', $rows, array( 'id', 'projeto', 'endereco' ) );
		WP_CLI::success(
			sprintf(
				/* translators: %d: número de projetos. */
				__( '%d projetos por geocodificar (--dry-run: sem fazer petiçons).', 'silveira' ),
				count( $pending )
			)
		);
		return;
	}

	$ok       = 0;
	$failed   = array();
	$previous = 0.0;
	foreach ( $pending as $id => $address ) {
		$wait = $previous + SILVEIRA_GEOCODE_CLI_INTERVAL - microtime( true );
		if ( $previous > 0 && $wait > 0 ) {
			usleep( (int) ceil( $wait * 1e6 ) );
		}
		$previous = microtime( true );
		$time     = gmdate( 'H:i:s', (int) $previous ) . sprintf( '.%03d', ( $previous - floor( $previous ) ) * 1000 );

		$result = silveira_geocode_apply( $id, $address );
		if ( is_wp_error( $result ) ) {
			$failed[] = $id;
		} else {
			++$ok;
		}
		$rows[] = array(
			'hora'      => $time,
			'id'        => $id,
			'projeto'   => get_the_title( $id ),
			'endereco'  => $address,
			'resultado' => is_wp_error( $result ) ? $result->get_error_code() : 'ok',
			'lat'       => is_wp_error( $result ) ? '' : $result['lat'],
			'lng'       => is_wp_error( $result ) ? '' : $result['lng'],
		);
	}

	\WP_CLI\Utils\format_items( 'table', $rows, array( 'hora', 'id', 'projeto', 'endereco', 'resultado', 'lat', 'lng' ) );
	$summary = sprintf(
		/* translators: 1: projetos geocodificados, 2: projetos com falhas. */
		__( '%1$d geocodificados, %2$d com falhas', 'silveira' ),
		$ok,
		count( $failed )
	);
	if ( $failed ) {
		WP_CLI::warning( $summary . ' (IDs: ' . implode( ', ', $failed ) . ').' );
	} else {
		WP_CLI::success( $summary . '.' );
	}
}

WP_CLI::add_command( 'silveira geocode', 'silveira_geocode_cli' );
