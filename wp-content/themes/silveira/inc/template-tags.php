<?php
/**
 * Theme template tags.
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
	$subtitle = __( 'Um ecossistema para o galego', 'silveira' ); // Default subtitle (Figma).
	$image    = '';

	if ( is_archive() ) {
		// No prefix ("Archives:", "Categoria de Recurso:"…, see silveira_archive_title_prefix) and without the <span>
		// WordPress wraps the name in: the hero shows it as plain text.
		$title = wp_strip_all_tags( get_the_archive_title() );

		if ( is_tax() || is_category() || is_tag() ) {
			$subtitle = get_the_archive_description();
			// Strip tags for simplicity in hero.
			$subtitle = wp_strip_all_tags( $subtitle );
		}
	} elseif ( is_search() ) {

		// Unescaped plain text: content-hero.php escapes it once when rendering.
		/* translators: %s: search term. */
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
 * Listing pagination with the theme's texts (core ones would show in the WordPress language).
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
 * Archive titles without prefix: the hero shows only the name ("Projetos", "Ames"…).
 *
 * @param string $prefix Default prefix.
 * @return string
 */
function silveira_archive_title_prefix( $prefix ) {
	unset( $prefix );
	return '';
}

/**
 * Context shown by a content card in listings.
 *
 * Projeto: modalidade and localidade (or comarca if it has no localidade); evento: territorio; recurso: category.
 *
 * @param WP_Post|int|null $post Content (defaults to the current one).
 * @return string Plain text, unescaped.
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
