<?php
/**
 * Custom Post Type: Evento
 */

function silveira_register_cpt_evento() {
	$labels = array(
		'name'                  => _x( 'Eventos', 'Post Type General Name', 'silveira' ),
		'singular_name'         => _x( 'Evento', 'Post Type Singular Name', 'silveira' ),
		'menu_name'             => __( 'Agenda', 'silveira' ),
		'all_items'             => __( 'Todos os Eventos', 'silveira' ),
		'add_new_item'          => __( 'Adicionar Novo Evento', 'silveira' ),
		'add_new'               => __( 'Adicionar Novo', 'silveira' ),
		'new_item'              => __( 'Novo Evento', 'silveira' ),
		'edit_item'             => __( 'Editar Evento', 'silveira' ),
		'update_item'           => __( 'Atualizar Evento', 'silveira' ),
		'view_item'             => __( 'Ver Evento', 'silveira' ),
		'search_items'          => __( 'Buscar Evento', 'silveira' ),
		'not_found'             => __( 'Não encontrado', 'silveira' ),
		'not_found_in_trash'    => __( 'Não encontrado na lixeira', 'silveira' ),
	);
	$args = array(
		'label'                 => __( 'Evento', 'silveira' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 6,
		'menu_icon'             => 'dashicons-calendar-alt',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => 'agenda',
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'rewrite'               => array( 'slug' => 'agenda' ),
		'show_in_rest'          => true,
	);
	register_post_type( 'evento', $args );
}
add_action( 'init', 'silveira_register_cpt_evento', 0 );
