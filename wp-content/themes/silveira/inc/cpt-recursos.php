<?php
/**
 * Custom Post Type: Recurso
 */

function silveira_register_cpt_recurso() {
	// Taxonomy: Categoria do Recurso
	$tax_labels = array(
		'name'                       => _x( 'Categorias de Recursos', 'Taxonomy General Name', 'silveira' ),
		'singular_name'              => _x( 'Categoria de Recurso', 'Taxonomy Singular Name', 'silveira' ),
		'menu_name'                  => __( 'Categorias', 'silveira' ),
		'all_items'                  => __( 'Todas as Categorias', 'silveira' ),
		'parent_item'                => __( 'Categoria Pai', 'silveira' ),
		'parent_item_colon'          => __( 'Categoria Pai:', 'silveira' ),
		'new_item_name'              => __( 'Nova Categoria', 'silveira' ),
		'add_new_item'               => __( 'Adicionar Nova Categoria', 'silveira' ),
		'edit_item'                  => __( 'Editar Categoria', 'silveira' ),
		'update_item'                => __( 'Atualizar Categoria', 'silveira' ),
		'view_item'                  => __( 'Ver Categoria', 'silveira' ),
	);
	$tax_args = array(
		'labels'                     => $tax_labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => false,
		'show_in_rest'               => true,
		'rewrite'                    => array( 'slug' => 'recursos' ),
	);
	register_taxonomy( 'categoria_recurso', array( 'recurso' ), $tax_args );

	// Post Type: Recurso
	$cpt_labels = array(
		'name'                  => _x( 'Recursos', 'Post Type General Name', 'silveira' ),
		'singular_name'         => _x( 'Recurso', 'Post Type Singular Name', 'silveira' ),
		'menu_name'             => __( 'Recursos', 'silveira' ),
		'all_items'             => __( 'Todos os Recursos', 'silveira' ),
		'add_new_item'          => __( 'Adicionar Novo Recurso', 'silveira' ),
		'add_new'               => __( 'Adicionar Novo', 'silveira' ),
		'new_item'              => __( 'Novo Recurso', 'silveira' ),
		'edit_item'             => __( 'Editar Recurso', 'silveira' ),
		'update_item'           => __( 'Atualizar Recurso', 'silveira' ),
		'view_item'             => __( 'Ver Recurso', 'silveira' ),
		'search_items'          => __( 'Buscar Recurso', 'silveira' ),
		'not_found'             => __( 'Não encontrado', 'silveira' ),
		'not_found_in_trash'    => __( 'Não encontrado na lixeira', 'silveira' ),
	);
	$cpt_args = array(
		'label'                 => __( 'Recurso', 'silveira' ),
		'labels'                => $cpt_labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 7,
		'menu_icon'             => 'dashicons-book',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => 'recursos',
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'rewrite'               => array( 'slug' => 'recursos/%categoria_recurso%' ), // Dynamic tag base
		'show_in_rest'          => true,
	);
	register_post_type( 'recurso', $cpt_args );
}
add_action( 'init', 'silveira_register_cpt_recurso', 0 );

/**
 * Filter post_type_link to swap %categoria_recurso% with the actual term slug
 */
function silveira_recurso_post_link( $post_link, $post ) {
	if ( is_object( $post ) && 'recurso' === $post->post_type ) {
		$terms = wp_get_object_terms( $post->ID, 'categoria_recurso' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			return str_replace( '%categoria_recurso%', $terms[0]->slug, $post_link );
		} else {
			// Fallback si el recurso no tiene categoría asignada
			return str_replace( '%categoria_recurso%', 'geral', $post_link );
		}
	}
	return $post_link;
}
add_filter( 'post_type_link', 'silveira_recurso_post_link', 1, 2 );
