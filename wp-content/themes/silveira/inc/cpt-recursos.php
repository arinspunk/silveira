<?php
/**
 * Custom Post Type: Recurso.
 *
 * @package Silveira
 */

/**
 * Registra custom post type: recurso.
 */
function silveira_register_cpt_recurso() {
	// Taxonomy: Categoria do Recurso.
	$tax_labels = array(
		'name'              => _x( 'Categorias de Recursos', 'Taxonomy General Name', 'silveira' ),
		'singular_name'     => _x( 'Categoria de Recurso', 'Taxonomy Singular Name', 'silveira' ),
		'menu_name'         => __( 'Categorias', 'silveira' ),
		'all_items'         => __( 'Todas as Categorias', 'silveira' ),
		'parent_item'       => __( 'Categoria Pai', 'silveira' ),
		'parent_item_colon' => __( 'Categoria Pai:', 'silveira' ),
		'new_item_name'     => __( 'Nova Categoria', 'silveira' ),
		'add_new_item'      => __( 'Adicionar Nova Categoria', 'silveira' ),
		'edit_item'         => __( 'Editar Categoria', 'silveira' ),
		'update_item'       => __( 'Atualizar Categoria', 'silveira' ),
		'view_item'         => __( 'Ver Categoria', 'silveira' ),
	);
	$tax_args   = array(
		'labels'            => $tax_labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => false,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'recursos' ),
		// Todo recurso tiene categoría: sin ninguna, WordPress le asigna «Geral» (y crea el término si no existe).
		'default_term'      => array(
			'name' => __( 'Geral', 'silveira' ),
			'slug' => 'geral',
		),
	);
	register_taxonomy( 'categoria_recurso', array( 'recurso' ), $tax_args );

	// Post Type: Recurso.
	$cpt_labels = array(
		'name'               => _x( 'Recursos', 'Post Type General Name', 'silveira' ),
		'singular_name'      => _x( 'Recurso', 'Post Type Singular Name', 'silveira' ),
		'menu_name'          => __( 'Recursos', 'silveira' ),
		'all_items'          => __( 'Todos os Recursos', 'silveira' ),
		'add_new_item'       => __( 'Adicionar Novo Recurso', 'silveira' ),
		'add_new'            => __( 'Adicionar Novo', 'silveira' ),
		'new_item'           => __( 'Novo Recurso', 'silveira' ),
		'edit_item'          => __( 'Editar Recurso', 'silveira' ),
		'update_item'        => __( 'Atualizar Recurso', 'silveira' ),
		'view_item'          => __( 'Ver Recurso', 'silveira' ),
		'search_items'       => __( 'Buscar Recurso', 'silveira' ),
		'not_found'          => __( 'Não encontrado', 'silveira' ),
		'not_found_in_trash' => __( 'Não encontrado na lixeira', 'silveira' ),
	);
	$cpt_args   = array(
		'label'               => __( 'Recurso', 'silveira' ),
		'labels'              => $cpt_labels,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 7,
		'menu_icon'           => 'dashicons-book',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => 'recursos',
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'rewrite'             => array( 'slug' => 'recursos/%categoria_recurso%' ), // Dynamic tag base.
		'show_in_rest'        => true,
	);
	register_post_type( 'recurso', $cpt_args );
}
add_action( 'init', 'silveira_register_cpt_recurso', 0 );

/**
 * Filter post_type_link to swap %categoria_recurso% with the actual term slug.
 *
 * @param string  $post_link Permalink del post.
 * @param WP_Post $post      Post.
 * @return string
 */
function silveira_recurso_post_link( $post_link, $post ) {
	if ( 'recurso' === $post->post_type ) {
		$terms = wp_get_object_terms( $post->ID, 'categoria_recurso' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			return str_replace( '%categoria_recurso%', $terms[0]->slug, $post_link );
		} else {
			// Fallback si el recurso no tiene categoría asignada.
			return str_replace( '%categoria_recurso%', 'geral', $post_link );
		}
	}
	return $post_link;
}
add_filter( 'post_type_link', 'silveira_recurso_post_link', 1, 2 );

/**
 * Asigna la categoría por defecto («geral») a un recurso que ha quedado sin ninguna.
 *
 * `default_term` cubre la creación sin categorías, pero no el caso en que la API REST (el editor de bloques)
 * envía la lista vacía de forma explícita, p. ej. al desmarcar la última categoría.
 *
 * @param WP_Post|int $post Recurso.
 */
function silveira_recurso_ensure_category( $post ) {
	$post = get_post( $post );
	if ( ! $post || 'recurso' !== $post->post_type || 'auto-draft' === $post->post_status ) {
		return;
	}
	$terms = wp_get_object_terms( $post->ID, 'categoria_recurso', array( 'fields' => 'ids' ) );
	if ( is_wp_error( $terms ) || ! empty( $terms ) ) {
		return;
	}
	$default = (int) get_option( 'default_term_categoria_recurso' );
	if ( $default ) {
		wp_set_object_terms( $post->ID, $default, 'categoria_recurso' );
	}
}
add_action( 'rest_after_insert_recurso', 'silveira_recurso_ensure_category' );

/**
 * Igual que silveira_recurso_ensure_category() para guardados fuera de la API REST.
 *
 * @param int $post_id ID del recurso.
 */
function silveira_recurso_ensure_category_on_save( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	silveira_recurso_ensure_category( $post_id );
}
add_action( 'save_post_recurso', 'silveira_recurso_ensure_category_on_save', 20 );
