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
	$subtitle = 'Um ecosistema para o galego'; // Default from Figma.
	$image    = '';

	if ( is_archive() ) {
		$title = get_the_archive_title();
		// Strip "Archive:" etc if needed.
		$title = str_replace( array( 'Archivo: ', 'Archive: ', 'Território: ' ), '', $title );

		if ( is_tax() || is_category() || is_tag() ) {
			$subtitle = get_the_archive_description();
			// Strip tags for simplicity in hero.
			$subtitle = wp_strip_all_tags( $subtitle );
		}
	} elseif ( is_search() ) {

		/* translators: %s: término de búsqueda. */
		$title = sprintf( esc_html__( 'Resultados para: %s', 'silveira' ), get_search_query() );
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
