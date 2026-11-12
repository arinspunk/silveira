<?php
/**
 * Geocodificación de proyectos con Nominatim (OpenStreetMap).
 *
 * @package Silveira
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( silveira_has_acf() ) {
	add_action( 'acf/save_post', 'silveira_geocode_project', 20 );
}

/**
 * Automate geocoding for Projects using Nominatim (OpenStreetMap).
 *
 * Solo geocodifica si faltan latitud o longitud.
 *
 * @param int|string $post_id ID del post que guarda ACF.
 */
function silveira_geocode_project( $post_id ) {
	// Only for 'projeto' post type.
	if ( get_post_type( $post_id ) !== 'projeto' ) {
		return;
	}

	$address = silveira_field( 'projeto_endereco', $post_id );

	if ( ! $address ) {
		return;
	}

	// Avoid infinite loop by removing action.
	remove_action( 'acf/save_post', 'silveira_geocode_project', 20 );

	// We only Geocode if latitude/longitude are empty.
	$lat = silveira_field( 'projeto_latitude', $post_id );
	$lng = silveira_field( 'projeto_longitude', $post_id );

	if ( empty( $lat ) || empty( $lng ) ) {

		// 1. Cleaning: Noise strings that confuse Nominatim.
		$noise         = array( 'Local', 'Bajo', 'Planta', 'Piso', 'Esquerda', 'Derecha', 'Izquierda' );
		$clean_address = str_ireplace( $noise, '', $address );

		// 2. Add Context (Galiza) to refine search.
		$search_query = $clean_address . ', Galiza, España';

		$url = 'https://nominatim.openstreetmap.org/search?format=json&q=' . rawurlencode( $search_query ) . '&limit=1';

		$response = wp_remote_get(
			$url,
			array(
				'user_agent' => 'Silveira-Theme-Geocoding (WordPress)',
			)
		);

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body );

			if ( ! empty( $data ) && isset( $data[0] ) ) {
				update_field( 'projeto_latitude', $data[0]->lat, $post_id );
				update_field( 'projeto_longitude', $data[0]->lon, $post_id );
			}
		}
	}

	// Re-add action.
	add_action( 'acf/save_post', 'silveira_geocode_project', 20 );
}
