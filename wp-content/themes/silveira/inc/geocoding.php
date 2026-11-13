<?php
/**
 * Geocodificación de proyectos con Nominatim (OpenStreetMap).
 *
 * - silveira_geocode_address(): busca una dirección y devuelve coordenadas o WP_Error.
 * - Al guardar un proyecto (acf/save_post) se decide si geocodificar según la meta privada
 *   `_silveira_geocode` (dirección y coordenadas del último geocoding), sin pisar coordenadas manuales.
 * - Si falla, la persona editora ve un aviso tras guardar (editor de bloques y clásico).
 *
 * @package Silveira
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Meta privada con el resultado del último geocoding: address, lat, lng, at. */
const SILVEIRA_GEOCODE_META = '_silveira_geocode';

/** Tolerancia para comparar coordenadas guardadas con las del último geocoding. */
const SILVEIRA_GEOCODE_EPSILON = 1e-6;

if ( silveira_has_acf() ) {
	add_action( 'acf/save_post', 'silveira_geocode_project', 20 );
	add_action( 'admin_notices', 'silveira_geocode_admin_notice' );
	add_action( 'rest_api_init', 'silveira_geocode_register_notice_route' );
	add_action( 'enqueue_block_editor_assets', 'silveira_geocode_enqueue_editor_notice' );
}

/**
 * Quita de la dirección las palabras de ruido de locales y pisos, solo como palabras completas
 * ("Localidade" se conserva), y colapsa espacios y comas repetidas.
 *
 * @param string $address Dirección tal como la escribió la persona editora.
 * @return string Dirección normalizada (la que se busca y se guarda en la meta).
 */
function silveira_geocode_normalize_address( $address ) {
	$noise = '(?:local|baixo|bajo|planta|piso|esquerda|esquerdo|direita|dereita|derecha|izquierda)';
	// Límites de palabra con letras Unicode: \b no trata "ú" o "ñ" como letras.
	$clean = (string) preg_replace( '/(?<![\p{L}\p{N}])' . $noise . '(?![\p{L}\p{N}])\.?/iu', '', (string) $address );
	$clean = (string) preg_replace( '/\s+/u', ' ', $clean );
	$clean = (string) preg_replace( '/\s*,(?:\s*,)*\s*/u', ', ', $clean ); // Comas repetidas o con espacios: una sola coma y un espacio.
	return trim( $clean, " ,\t\n\r\0\x0B" );
}

/**
 * Caja de búsqueda: Galiza incluida la Galiza exterior (Eo-Navia, Bierzo, As Portelas, Seabra).
 *
 * @return float[] [oeste, norte, este, sur], como el parámetro viewbox de Nominatim.
 */
function silveira_geocode_viewbox() {
	/**
	 * Filtra la caja geográfica en que se aceptan resultados del geocoding.
	 *
	 * @param float[] $viewbox [oeste, norte, este, sur].
	 */
	return array_map( 'floatval', (array) apply_filters( 'silveira_geocode_viewbox', array( -9.35, 43.85, -6.10, 41.80 ) ) );
}

/**
 * User-Agent con que el sitio se identifica ante Nominatim (exigido por su política de uso).
 *
 * @return string
 */
function silveira_geocode_user_agent() {
	return sprintf( 'Silveira/%s (+%s)', wp_get_theme( 'silveira' )->get( 'Version' ), home_url( '/' ) );
}

/**
 * Busca las coordenadas de una dirección en Nominatim.
 *
 * Códigos de error: silveira_geocode_empty, silveira_geocode_http (red o tiempo límite),
 * silveira_geocode_status (HTTP != 200), silveira_geocode_invalid (respuesta no válida),
 * silveira_geocode_not_found (sin resultados), silveira_geocode_outside (fuera de la caja).
 *
 * @param string $address Dirección (se normaliza aquí).
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
 * ¿Hay que geocodificar el proyecto al guardar? Tabla de decisión (design D1).
 *
 * @param string     $address Dirección normalizada.
 * @param mixed      $lat     Latitud guardada.
 * @param mixed      $lng     Longitud guardada.
 * @param array|null $last    Meta del último geocoding, o null si no hay.
 * @return bool
 */
function silveira_geocode_should_run( $address, $lat, $lng, $last ) {
	if ( '' === $address ) {
		return false;
	}
	if ( ! is_numeric( $lat ) || ! is_numeric( $lng ) ) {
		return true; // Sin coordenadas.
	}
	if ( ! is_array( $last ) || ! isset( $last['address'], $last['lat'], $last['lng'] ) ) {
		return false; // Coordenadas sin geocoding previo conocido: se tratan como manuales.
	}
	if ( $address === $last['address'] ) {
		return false; // Misma dirección.
	}
	// Dirección nueva: solo si las coordenadas siguen siendo las del último geocoding (no se fijaron a mano).
	return abs( (float) $lat - (float) $last['lat'] ) < SILVEIRA_GEOCODE_EPSILON
		&& abs( (float) $lng - (float) $last['lng'] ) < SILVEIRA_GEOCODE_EPSILON;
}

/**
 * Geocodifica un proyecto y guarda coordenadas y meta. No consulta la tabla de decisión.
 *
 * @param int    $post_id ID del proyecto.
 * @param string $address Dirección (sin normalizar o normalizada).
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
 * Geocoding al guardar un proyecto (acf/save_post, después de que ACF guarde los campos).
 *
 * @param int|string $post_id ID del post que guarda ACF.
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
 * Texto del aviso según el motivo del fallo.
 *
 * @param string $reason Código de error de silveira_geocode_address().
 * @return string
 */
function silveira_geocode_notice_message( $reason ) {
	if ( in_array( $reason, array( 'silveira_geocode_not_found', 'silveira_geocode_outside' ), true ) ) {
		return __( 'Nom se encontrárom coordenadas na Galiza para o endereço deste projeto, polo que nom aparecerá no mapa ou ficará no ponto anterior. Revê o endereço ou introduz a latitude e a longitude manualmente.', 'silveira' );
	}
	return __( 'Nom foi possível contactar com o serviço de geolocalizaçom e as coordenadas do projeto nom mudárom. Volta guardar o projeto mais tarde ou introduz a latitude e a longitude manualmente.', 'silveira' );
}

/**
 * Lee y borra el aviso pendiente de la persona usuaria actual para un proyecto.
 *
 * @param int $post_id ID del proyecto.
 * @return string Mensaje, o cadena vacía si no hay aviso.
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
 * Editor clásico: el guardado recarga la página y el aviso sale en admin_notices.
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
 * Editor de bloques: ACF guarda en una petición aparte, sin recargar; el script del editor
 * pide el aviso a esta ruta cuando termina de guardar las meta boxes.
 */
function silveira_geocode_register_notice_route() {
	register_rest_route(
		'silveira/v1',
		'/geocode-notice/(?P<id>\d+)',
		array(
			'methods'             => 'POST', // Consume el aviso.
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
 * Script del editor de bloques para proyectos: muestra el aviso de geocoding tras guardar.
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
