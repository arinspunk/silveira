<?php
/**
 * Shared Taxonomies
 */

function silveira_register_shared_taxonomies() {
	$labels = array(
		'name'                       => _x( 'Territórios', 'Taxonomy General Name', 'silveira' ),
		'singular_name'              => _x( 'Território', 'Taxonomy Singular Name', 'silveira' ),
		'menu_name'                  => __( 'Território', 'silveira' ),
		'all_items'                  => __( 'Todos os Territórios', 'silveira' ),
		'parent_item'                => __( 'Comarca', 'silveira' ),
		'parent_item_colon'          => __( 'Comarca:', 'silveira' ),
		'new_item_name'              => __( 'Novo Território', 'silveira' ),
		'add_new_item'               => __( 'Adicionar Novo Território', 'silveira' ),
		'edit_item'                  => __( 'Editar Território', 'silveira' ),
		'update_item'                => __( 'Atualizar Território', 'silveira' ),
		'view_item'                  => __( 'Ver Território', 'silveira' ),
		'search_items'               => __( 'Buscar Territórios', 'silveira' ),
		'not_found'                  => __( 'Não encontrado', 'silveira' ),
		'no_terms'                   => __( 'Sem territórios', 'silveira' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => false,
		'show_in_rest'               => true,
	);
	// Asociar 'territorio' tanto a 'projeto' como a 'evento'
	register_taxonomy( 'territorio', array( 'projeto', 'evento' ), $args );
}
add_action( 'init', 'silveira_register_shared_taxonomies', 0 );
