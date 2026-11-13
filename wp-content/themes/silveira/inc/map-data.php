<?php
/**
 * Project data for the front page map.
 *
 * @package Silveira
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalizes a value to plain text: no tags and with entities decoded.
 *
 * Escaping is up to the renderer (map.js uses textContent).
 *
 * @param mixed $value Source value (string, null or false).
 * @return string
 */
function silveira_map_plain_text( $value ) {
	if ( ! is_scalar( $value ) ) {
		return '';
	}
	return html_entity_decode( wp_strip_all_tags( (string) $value ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
}

/**
 * Sanitizes a URL for the map; returns '' if it is invalid or not http/https.
 *
 * @param mixed $value Source URL.
 * @return string
 */
function silveira_map_url( $value ) {
	if ( ! is_string( $value ) || '' === $value ) {
		return '';
	}
	return esc_url_raw( $value, array( 'http', 'https' ) );
}

/**
 * Published projects with coordinates, ready for the map.
 *
 * @return array<int, array<string, mixed>>
 */
function silveira_get_map_points() {
	$projects_query = new WP_Query(
		array(
			'post_type'      => 'projeto',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			// Stable order: with equal dates, MySQL may return projects in any order.
			'orderby'        => array(
				'date' => 'DESC',
				'ID'   => 'DESC',
			),
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

		// Categories for filtering.
		// get_the_terms() uses the term cache WP_Query has already primed (no per-project queries).
		$modalidades_obj = get_the_terms( $post_id, 'modalidade_projeto' );
		$modalidades     = ( $modalidades_obj && ! is_wp_error( $modalidades_obj ) ) ? wp_list_pluck( $modalidades_obj, 'slug' ) : array();
		$mod_name        = $modalidades ? $modalidades_obj[0]->name : '';

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

				// Also include the parent comarca for the filters.
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
			'modalidades' => array_values( $modalidades ),
			'territorios' => array_values( array_unique( $territorio_slugs ) ),
		);
	}
	wp_reset_postdata();

	return $map_points;
}

add_action( 'wp_enqueue_scripts', 'silveira_enqueue_map_points', 30 );

/**
 * Passes the map points attached to the main script (front page only).
 *
 * JSON_HEX_* prevents the content from closing the <script> or breaking attributes.
 */
function silveira_enqueue_map_points() {
	if ( ! is_front_page() ) {
		return;
	}

	$json = wp_json_encode(
		silveira_get_map_points(),
		JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
	);

	$config = wp_json_encode(
		array( 'tiles' => silveira_map_tiles() ),
		JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
	);

	$i18n = wp_json_encode( silveira_map_i18n(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );

	wp_add_inline_script(
		'silveira-main',
		'window.silveiraMapPoints = ' . ( false === $json ? '[]' : $json ) . ';'
			. 'window.silveiraMapConfig = ' . ( false === $config ? '{}' : $config ) . ';'
			. 'window.silveiraI18n = ' . ( false === $i18n ? '{}' : $i18n ) . ';',
		'before'
	);
}

/**
 * Map texts for map.js, already translated (gettext, silveira domain).
 *
 * Plurals are sent as two %d templates (singular and plural) because JS has no _n().
 *
 * @return array<string, string>
 */
function silveira_map_i18n() {
	return array(
		'viewProject'      => __( 'Ver projeto', 'silveira' ),
		'showList'         => __( 'Ver a lista', 'silveira' ),
		'hideList'         => __( 'Acochar a lista', 'silveira' ),
		'noResults'        => __( 'Nom há projetos para estes filtros.', 'silveira' ),
		/* translators: %d: number of options checked in a filter (1). */
		'selectedSingular' => _n( '%d selecionada', '%d selecionadas', 1, 'silveira' ),
		/* translators: %d: number of options checked in a filter (2 or more). */
		'selectedPlural'   => _n( '%d selecionada', '%d selecionadas', 2, 'silveira' ),
	);
}

/**
 * Tile provider for the front page map.
 *
 * The attribution is site HTML (not editor content): Leaflet inserts it as is.
 * To use another provider, filter `silveira_map_tiles` with its URL, attribution and max zoom.
 *
 * @return array{url: string, attribution: string, maxZoom: int}
 */
function silveira_map_tiles() {
	$defaults = array(
		'url'         => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
		'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
		'maxZoom'     => 19,
	);

	/**
	 * Filters the map tile provider.
	 *
	 * @param array<string, mixed> $tiles url (with {z}/{x}/{y}), attribution (HTML) and maxZoom; missing keys take the default value.
	 */
	$tiles = array_merge( $defaults, (array) apply_filters( 'silveira_map_tiles', $defaults ) );

	return array(
		'url'         => (string) $tiles['url'],
		'attribution' => (string) $tiles['attribution'],
		'maxZoom'     => (int) $tiles['maxZoom'],
	);
}
