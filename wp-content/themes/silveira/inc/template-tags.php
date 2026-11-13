<?php
/**
 * Etiquetas de plantilla del theme.
 *
 * @package Silveira
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper to render the hero component with context-aware defaults
 */
function silveira_hero() {
	$title    = '';
	$subtitle = __( 'Um ecossistema para o galego', 'silveira' ); // Subtítulo por defecto (Figma).
	$image    = '';

	if ( is_archive() ) {
		// Sin prefijo ("Archives:", "Categoria de Recurso:"…, ver silveira_archive_title_prefix) y sin el <span>
		// con que WordPress envuelve el nombre: el hero lo muestra como texto.
		$title = wp_strip_all_tags( get_the_archive_title() );

		if ( is_tax() || is_category() || is_tag() ) {
			$subtitle = get_the_archive_description();
			// Strip tags for simplicity in hero.
			$subtitle = wp_strip_all_tags( $subtitle );
		}
	} elseif ( is_search() ) {

		// Texto plano sin escapar: content-hero.php lo escapa una sola vez al pintarlo.
		/* translators: %s: término de búsqueda. */
		$title = sprintf( __( 'Resultados para: %s', 'silveira' ), get_search_query( false ) );
	} elseif ( is_singular() ) {
		$title = get_the_title();
		$image = get_the_post_thumbnail_url( get_the_ID(), 'full' );

		if ( is_singular( 'projeto' ) ) {
			$post_id     = get_the_ID();
			$modalidades = get_the_terms( $post_id, 'modalidade_projeto' );
			$territorios = get_the_terms( $post_id, 'territorio' );

			$mod_part = ! empty( $modalidades ) ? implode( ', ', wp_list_pluck( $modalidades, 'name' ) ) : '';

			$loc_names = array();
			$com_names = array();

			if ( ! empty( $territorios ) && ! is_wp_error( $territorios ) ) {
				foreach ( $territorios as $term ) {
					if ( 0 !== (int) $term->parent ) {
						$loc_names[] = $term->name;
						$parent      = get_term( $term->parent, 'territorio' );
						if ( $parent && ! is_wp_error( $parent ) ) {
							$com_names[] = $parent->name;
						}
					} else {
						$com_names[] = $term->name;
					}
				}
			}

			$loc_part = implode( ', ', array_unique( $loc_names ) );
			$com_part = implode( ', ', array_unique( $com_names ) );

			$parts = array();
			if ( $mod_part ) {
				$parts[] = $mod_part;
			}
			if ( $loc_part ) {
				$parts[] = $loc_part;
			}

			$subtitle = implode( ' – ', $parts );
			if ( $com_part ) {
				$subtitle .= ' (' . $com_part . ')';
			}
		}
	} elseif ( is_home() ) {
		$title = get_the_title( get_option( 'page_for_posts' ) );
	} elseif ( is_404() ) {
		$title = __( 'Página nom encontrada', 'silveira' );
	}

	get_template_part(
		'template-parts/content',
		'hero',
		array(
			'title'    => $title,
			'subtitle' => $subtitle,
			'image'    => $image ? $image : '', // Fallback to empty if no image.
		)
	);
}

/**
 * Paginación de los listados con textos del theme (los del núcleo saldrían en el idioma de WordPress).
 */
function silveira_posts_pagination() {
	the_posts_pagination(
		array(
			'prev_text'          => __( 'Anterior', 'silveira' ),
			'next_text'          => __( 'Seguinte', 'silveira' ),
			'screen_reader_text' => __( 'Navegaçom entre páginas', 'silveira' ),
			'aria_label'         => __( 'Páginas', 'silveira' ),
		)
	);
}

add_filter( 'get_the_archive_title_prefix', 'silveira_archive_title_prefix' );
/**
 * Títulos de archivo sin prefijo: el hero muestra solo el nombre ("Projetos", "Ames"…).
 *
 * @param string $prefix Prefijo por defecto.
 * @return string
 */
function silveira_archive_title_prefix( $prefix ) {
	unset( $prefix );
	return '';
}

/**
 * Contexto que muestra la tarjeta de un contenido en los listados.
 *
 * Proyecto: modalidade y localidade (o comarca si no tiene localidade); evento: territorio; recurso: categoría.
 *
 * @param WP_Post|int|null $post Contenido (por defecto, el actual).
 * @return string Texto plano, sin escapar.
 */
function silveira_card_context( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}

	$names = static function ( $taxonomy, $filter = null ) use ( $post ) {
		$terms = get_the_terms( $post, $taxonomy );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return array();
		}
		if ( $filter ) {
			$terms = array_filter( $terms, $filter );
		}
		return wp_list_pluck( $terms, 'name' );
	};

	switch ( $post->post_type ) {
		case 'projeto':
			$localidades = $names(
				'territorio',
				static function ( $term ) {
					return 0 !== (int) $term->parent;
				}
			);
			$parts       = array_merge(
				$names( 'modalidade_projeto' ),
				$localidades ? $localidades : $names( 'territorio' )
			);
			break;
		case 'evento':
			$parts = $names( 'territorio' );
			break;
		case 'recurso':
			$parts = $names( 'categoria_recurso' );
			break;
		default:
			$parts = array();
	}

	return implode( ' · ', array_unique( $parts ) );
}
