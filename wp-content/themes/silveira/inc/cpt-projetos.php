<?php
/**
 * Custom Post Type: Projeto
 */

function silveira_register_cpt_projeto() {
	// Exclusiva Taxonomy: Modalidade
	$tax_labels = array(
		'name'                       => _x( 'Modalidades', 'Taxonomy General Name', 'silveira' ),
		'singular_name'              => _x( 'Modalidade', 'Taxonomy Singular Name', 'silveira' ),
		'menu_name'                  => __( 'Modalidade', 'silveira' ),
		'all_items'                  => __( 'Todas as Modalidades', 'silveira' ),
		'new_item_name'              => __( 'Nova Modalidade', 'silveira' ),
		'add_new_item'               => __( 'Adicionar Nova Modalidade', 'silveira' ),
		'edit_item'                  => __( 'Editar Modalidade', 'silveira' ),
		'update_item'                => __( 'Atualizar Modalidade', 'silveira' ),
		'view_item'                  => __( 'Ver Modalidade', 'silveira' ),
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
	);
	register_taxonomy( 'modalidade_projeto', array( 'projeto' ), $tax_args );

	// Post Type: Projeto
	$cpt_labels = array(
		'name'                  => _x( 'Projetos', 'Post Type General Name', 'silveira' ),
		'singular_name'         => _x( 'Projeto', 'Post Type Singular Name', 'silveira' ),
		'menu_name'             => __( 'Projetos', 'silveira' ),
		'all_items'             => __( 'Todos os Projetos', 'silveira' ),
		'add_new_item'          => __( 'Adicionar Novo Projeto', 'silveira' ),
		'add_new'               => __( 'Adicionar Novo', 'silveira' ),
		'new_item'              => __( 'Novo Projeto', 'silveira' ),
		'edit_item'             => __( 'Editar Projeto', 'silveira' ),
		'update_item'           => __( 'Atualizar Projeto', 'silveira' ),
		'view_item'             => __( 'Ver Projeto', 'silveira' ),
		'search_items'          => __( 'Buscar Projeto', 'silveira' ),
		'not_found'             => __( 'Não encontrado', 'silveira' ),
		'not_found_in_trash'    => __( 'Não encontrado na lixeira', 'silveira' ),
	);
	$cpt_args = array(
		'label'                 => __( 'Projeto', 'silveira' ),
		'labels'                => $cpt_labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-portfolio',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'rewrite'               => array( 'slug' => 'projeto' ),
		'show_in_rest'          => true,
	);
	register_post_type( 'projeto', $cpt_args );
}
add_action( 'init', 'silveira_register_cpt_projeto', 0 );
