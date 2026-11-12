<?php
/**
 * Datos de proyectos para el mapa de la portada.
 *
 * @package Silveira
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normaliza un valor a texto plano: sin etiquetas y con entidades decodificadas.
 *
 * El escape corresponde a quien pinta (map.js usa textContent).
 *
 * @param mixed $value Valor de origen (string, null o false).
 * @return string
 */
function silveira_map_plain_text( $value ) {
	if ( ! is_scalar( $value ) ) {
		return '';
	}
	return html_entity_decode( wp_strip_all_tags( (string) $value ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}

/**
 * Sanea una URL para el mapa; devuelve '' si no es válida o no es http/https.
 *
 * @param mixed $value URL de origen.
 * @return string
 */
function silveira_map_url( $value ) {
	if ( ! is_string( $value ) || '' === $value ) {
		return '';
	}
	return esc_url_raw( $value, array( 'http', 'https' ) );
}

/**
 * Proyectos publicados con coordenadas, listos para el mapa.
 *
 * @return array<int, array<string, mixed>>
 */
function silveira_get_map_points() {
	$projects_query = new WP_Query(
		array(
			'post_type'      => 'projeto',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		)
	);

	$map_points = array();

	while ( $projects_query->have_posts() ) {
		$projects_query->the_post();
		$post_id = get_the_ID();
		$lat     = silveira_field( 'projeto_latitude' );
		$lng     = silveira_field( 'projeto_longitude' );

		if ( ! $lat || ! $lng ) {
			continue;
		}

		// Categorías para filtrar.
		$modalidades     = wp_get_post_terms( $post_id, 'modalidade_projeto', array( 'fields' => 'slugs' ) );
		$modalidades_obj = get_the_terms( $post_id, 'modalidade_projeto' );
		$mod_name        = ( $modalidades_obj && ! is_wp_error( $modalidades_obj ) ) ? $modalidades_obj[0]->name : '';

		$territorios_obj  = get_the_terms( $post_id, 'territorio' );
		$territorio_slugs = array();
		$localidade_name  = '';
		$comarca_name     = '';

		if ( $territorios_obj && ! is_wp_error( $territorios_obj ) ) {
			foreach ( $territorios_obj as $term ) {
				$territorio_slugs[] = $term->slug;

				if ( 0 === (int) $term->parent ) {
					$comarca_name = $term->name;
					continue;
				}

				// Incluir también a comarca pai para os filtros.
				$parent = get_term( $term->parent, 'territorio' );
				if ( $parent instanceof WP_Term ) {
					$territorio_slugs[] = $parent->slug;
				}
				$localidade_name = $term->name;
			}
		}

		$overline_parts = array();
		if ( $mod_name ) {
			$overline_parts[] = $mod_name;
		}
		if ( $localidade_name ) {
			$overline_parts[] = 'em ' . $localidade_name;
		}
		if ( $comarca_name ) {
			$overline_parts[] = '(' . $comarca_name . ')';
		}

		$map_points[] = array(
			'id'          => $post_id,
			'title'       => silveira_map_plain_text( get_the_title() ),
			'excerpt'     => silveira_map_plain_text( get_the_excerpt() ),
			'overline'    => silveira_map_plain_text( implode( ' ', $overline_parts ) ),
			'lat'         => (float) $lat,
			'lng'         => (float) $lng,
			'url'         => silveira_map_url( get_permalink() ),
			'lema'        => silveira_map_plain_text( silveira_field( 'projeto_lema' ) ),
			'image'       => silveira_map_url( get_the_post_thumbnail_url( $post_id, 'large' ) ),
			'modalidades' => is_array( $modalidades ) ? $modalidades : array(),
			'territorios' => array_values( array_unique( $territorio_slugs ) ),
		);
	}
	wp_reset_postdata();

	return $map_points;
}

add_action( 'wp_enqueue_scripts', 'silveira_enqueue_map_points', 30 );

/**
 * Entrega los puntos del mapa ligados al script principal (solo en la portada).
 *
 * JSON_HEX_* impide que el contenido cierre el <script> o rompa atributos.
 */
function silveira_enqueue_map_points() {
	if ( ! is_front_page() ) {
		return;
	}

	$json = wp_json_encode(
		silveira_get_map_points(),
		JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
	);

	wp_add_inline_script(
		'silveira-main',
		'window.silveiraMapPoints = ' . ( false === $json ? '[]' : $json ) . ';',
		'before'
	);
}
