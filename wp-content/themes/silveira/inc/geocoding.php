<?php
/**
 * Project geocoding with Nominatim (OpenStreetMap).
 *
 * - silveira_geocode_address(): looks up an address and returns coordinates or WP_Error.
 * - When a project is saved (acf/save_post), whether to geocode is decided from the private meta
 *   `_silveira_geocode` (address and coordinates of the last geocoding), never overwriting manual coordinates.
 * - On failure, the editor sees a notice after saving (block and classic editor).
 *
 * @package Silveira
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Private meta holding the last geocoding result: address, lat, lng, at. */
const SILVEIRA_GEOCODE_META = '_silveira_geocode';

/** Tolerance for comparing saved coordinates with those of the last geocoding. */
const SILVEIRA_GEOCODE_EPSILON = 1e-6;

if ( silveira_has_acf() ) {
	add_action( 'acf/save_post', 'silveira_geocode_project', 20 );
	add_action( 'admin_notices', 'silveira_geocode_admin_notice' );
	add_action( 'rest_api_init', 'silveira_geocode_register_notice_route' );
	add_action( 'enqueue_block_editor_assets', 'silveira_geocode_enqueue_editor_notice' );
}

/**
 * Strips noise words for premises and floors from the address, only as whole words
 * ("Localidade" is kept), and collapses repeated spaces and commas.
 *
 * @param string $address Address as typed by the editor.
 * @return string Normalized address (the one looked up and stored in the meta).
 */
function silveira_geocode_normalize_address( $address ) {
	$noise = '(?:local|baixo|bajo|planta|piso|esquerda|esquerdo|direita|dereita|derecha|izquierda)';
	// Word boundaries with Unicode letters: \b does not treat "ú" or "ñ" as letters.
	$clean = (string) preg_replace( '/(?<![\p{L}\p{N}])' . $noise . '(?![\p{L}\p{N}])\.?/iu', '', (string) $address );
	$clean = (string) preg_replace( '/\s+/u', ' ', $clean );
	$clean = (string) preg_replace( '/\s*,(?:\s*,)*\s*/u', ', ', $clean ); // Repeated or spaced commas: a single comma and one space.
	return trim( $clean, " ,\t\n\r\0\x0B" );
}

/**
 * Search box: Galiza including the outer Galiza (Eo-Navia, Bierzo, As Portelas, Seabra).
 *
 * @return float[] [west, north, east, south], like Nominatim's viewbox parameter.
 */
function silveira_geocode_viewbox() {
	/**
	 * Filters the geographic box within which geocoding results are accepted.
	 *
	 * @param float[] $viewbox [west, north, east, south].
	 */
	return array_map( 'floatval', (array) apply_filters( 'silveira_geocode_viewbox', array( -9.35, 43.85, -6.10, 41.80 ) ) );
}

/**
 * User-Agent the site identifies itself with to Nominatim (required by its usage policy).
 *
 * @return string
 */
function silveira_geocode_user_agent() {
	return sprintf( 'Silveira/%s (+%s)', wp_get_theme( 'silveira' )->get( 'Version' ), home_url( '/' ) );
}

/**
 * Looks up the coordinates of an address in Nominatim.
 *
 * Error codes: silveira_geocode_empty, silveira_geocode_http (network or timeout),
 * silveira_geocode_status (HTTP != 200), silveira_geocode_invalid (invalid response),
 * silveira_geocode_not_found (no results), silveira_geocode_outside (outside the box).
 *
 * @param string $address Address (normalized here).
 * @return array{lat: float, lng: float}|WP_Error
 */
function silveira_geocode_address( $address ) {
	$query = silveira_geocode_normalize_address( $address );
	if ( '' === $query ) {
		return new WP_Error( 'silveira_geocode_empty', 'Empty address.' );
	}

	$viewbox = silveira_geocode_viewbox();
	$args    = array(
		'format'       => 'jsonv2',
		'q'            => $query,
		'limit'        => 1,
		'countrycodes' => 'es',
		'viewbox'      => implode( ',', $viewbox ),
		'bounded'      => 1,
	);
	if ( defined( 'SILVEIRA_NOMINATIM_EMAIL' ) && is_email( SILVEIRA_NOMINATIM_EMAIL ) ) {
		$args['email'] = SILVEIRA_NOMINATIM_EMAIL;
	}

	$response = wp_remote_get(
		add_query_arg( array_map( 'rawurlencode', $args ), 'https://nominatim.openstreetmap.org/search' ),
		array(
			'timeout'    => 8,
			'user-agent' => silveira_geocode_user_agent(),
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'silveira_geocode_http', $response->get_error_message() );
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		return new WP_Error( 'silveira_geocode_status', sprintf( 'HTTP %d', $code ) );
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'silveira_geocode_invalid', 'Invalid JSON.' );
	}
	if ( empty( $data ) ) {
		return new WP_Error( 'silveira_geocode_not_found', 'No results.' );
	}
	$first = $data[0] ?? null;
	if ( ! is_array( $first ) || ! isset( $first['lat'], $first['lon'] ) || ! is_numeric( $first['lat'] ) || ! is_numeric( $first['lon'] ) ) {
		return new WP_Error( 'silveira_geocode_invalid', 'Result without numeric coordinates.' );
	}

	$lat                                 = (float) $first['lat'];
	$lng                                 = (float) $first['lon'];
	list( $west, $north, $east, $south ) = $viewbox;
	if ( $lng < $west || $lng > $east || $lat < $south || $lat > $north ) {
		return new WP_Error( 'silveira_geocode_outside', sprintf( 'Result outside the box: %F,%F', $lat, $lng ) );
	}

	return array(
		'lat' => $lat,
		'lng' => $lng,
	);
}

/**
 * Should the project be geocoded on save? Decision table (design D1).
 *
 * @param string     $address Normalized address.
 * @param mixed      $lat     Saved latitude.
 * @param mixed      $lng     Saved longitude.
 * @param array|null $last    Last geocoding meta, or null if none.
 * @return bool
 */
function silveira_geocode_should_run( $address, $lat, $lng, $last ) {
	if ( '' === $address ) {
		return false;
	}
	if ( ! is_numeric( $lat ) || ! is_numeric( $lng ) ) {
		return true; // No coordinates.
	}
	if ( ! is_array( $last ) || ! isset( $last['address'], $last['lat'], $last['lng'] ) ) {
		return false; // Coordinates with no known previous geocoding: treated as manual.
	}
	if ( $address === $last['address'] ) {
		return false; // Same address.
	}
	// New address: only if the coordinates are still those of the last geocoding (not set by hand).
	return abs( (float) $lat - (float) $last['lat'] ) < SILVEIRA_GEOCODE_EPSILON
		&& abs( (float) $lng - (float) $last['lng'] ) < SILVEIRA_GEOCODE_EPSILON;
}

/**
 * Geocodes a project and saves coordinates and meta. Does not check the decision table.
 *
 * @param int    $post_id Project ID.
 * @param string $address Address (raw or normalized).
 * @return array{lat: float, lng: float}|WP_Error
 */
function silveira_geocode_apply( $post_id, $address ) {
	$result = silveira_geocode_address( $address );
	if ( is_wp_error( $result ) ) {
		return $result;
	}
	update_field( 'projeto_latitude', $result['lat'], $post_id );
	update_field( 'projeto_longitude', $result['lng'], $post_id );
	update_post_meta(
		$post_id,
		SILVEIRA_GEOCODE_META,
		array(
			'address' => silveira_geocode_normalize_address( $address ),
			'lat'     => $result['lat'],
			'lng'     => $result['lng'],
			'at'      => time(),
		)
	);
	return $result;
}

/**
 * Geocoding on project save (acf/save_post, after ACF saves the fields).
 *
 * @param int|string $post_id ID of the post ACF is saving.
 */
function silveira_geocode_project( $post_id ) {
	if ( ! is_numeric( $post_id ) || get_post_type( (int) $post_id ) !== 'projeto' ) {
		return;
	}
	$post_id = (int) $post_id;
	$address = silveira_geocode_normalize_address( (string) silveira_field( 'projeto_endereco', $post_id ) );
	$last    = get_post_meta( $post_id, SILVEIRA_GEOCODE_META, true );

	if ( ! silveira_geocode_should_run(
		$address,
		silveira_field( 'projeto_latitude', $post_id ),
		silveira_field( 'projeto_longitude', $post_id ),
		is_array( $last ) ? $last : null
	) ) {
		return;
	}

	$result = silveira_geocode_apply( $post_id, $address );
	if ( is_wp_error( $result ) ) {
		set_transient(
			'silveira_geocode_notice_' . get_current_user_id(),
			array(
				'post_id' => $post_id,
				'reason'  => $result->get_error_code(),
			),
			60
		);
	}
}

/**
 * Notice text depending on the failure reason.
 *
 * @param string $reason Error code from silveira_geocode_address().
 * @return string
 */
function silveira_geocode_notice_message( $reason ) {
	if ( in_array( $reason, array( 'silveira_geocode_not_found', 'silveira_geocode_outside' ), true ) ) {
		return __( 'Nom se encontrárom coordenadas na Galiza para o endereço deste projeto, polo que nom aparecerá no mapa ou ficará no ponto anterior. Revê o endereço ou introduz a latitude e a longitude manualmente.', 'silveira' );
	}
	return __( 'Nom foi possível contactar com o serviço de geolocalizaçom e as coordenadas do projeto nom mudárom. Volta guardar o projeto mais tarde ou introduz a latitude e a longitude manualmente.', 'silveira' );
}

/**
 * Reads and deletes the current user's pending notice for a project.
 *
 * @param int $post_id Project ID.
 * @return string Message, or empty string if there is no notice.
 */
function silveira_geocode_take_notice( $post_id ) {
	$key    = 'silveira_geocode_notice_' . get_current_user_id();
	$notice = get_transient( $key );
	if ( ! is_array( $notice ) || (int) ( $notice['post_id'] ?? 0 ) !== (int) $post_id ) {
		return '';
	}
	delete_transient( $key );
	return silveira_geocode_notice_message( (string) ( $notice['reason'] ?? '' ) );
}

/**
 * Classic editor: saving reloads the page and the notice shows in admin_notices.
 */
function silveira_geocode_admin_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'post' !== $screen->base || 'projeto' !== $screen->post_type || $screen->is_block_editor() ) {
		return;
	}
	$message = silveira_geocode_take_notice( (int) get_the_ID() );
	if ( '' !== $message ) {
		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', esc_html( $message ) );
	}
}

/**
 * Block editor: ACF saves in a separate request, without reloading; the editor script
 * requests the notice from this route once the meta boxes finish saving.
 */
function silveira_geocode_register_notice_route() {
	register_rest_route(
		'silveira/v1',
		'/geocode-notice/(?P<id>\d+)',
		array(
			'methods'             => 'POST', // Consumes the notice.
			'callback'            => static function ( WP_REST_Request $request ) {
				return array( 'message' => silveira_geocode_take_notice( (int) $request['id'] ) );
			},
			'permission_callback' => static function ( WP_REST_Request $request ) {
				return current_user_can( 'edit_post', (int) $request['id'] );
			},
		)
	);
}

/**
 * Block editor script for projects: shows the geocoding notice after saving.
 */
function silveira_geocode_enqueue_editor_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'projeto' !== $screen->post_type ) {
		return;
	}
	wp_register_script( 'silveira-geocode-notice', false, array( 'wp-data', 'wp-api-fetch', 'wp-notices', 'wp-editor', 'wp-edit-post' ), wp_get_theme( 'silveira' )->get( 'Version' ), true );
	wp_enqueue_script( 'silveira-geocode-notice' );
	wp_add_inline_script(
		'silveira-geocode-notice',
		<<<'JS'
( function ( wp ) {
	var wasSaving = false;
	wp.data.subscribe( function () {
		var editor = wp.data.select( 'core/editor' );
		var saving = editor.isSavingPost() || editor.isAutosavingPost() || wp.data.select( 'core/edit-post' ).isSavingMetaBoxes();
		if ( wasSaving && ! saving ) {
			wp.apiFetch( { path: '/silveira/v1/geocode-notice/' + editor.getCurrentPostId(), method: 'POST' } ).then( function ( res ) {
				if ( res && res.message ) {
					wp.data.dispatch( 'core/notices' ).createWarningNotice( res.message, { id: 'silveira-geocode', isDismissible: true } );
				}
			} );
		}
		wasSaving = saving;
	} );
} )( window.wp );
JS
	);
}
