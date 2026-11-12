<?php
/**
 * Setup del theme: soportes, menús, flush de reglas y clases BEM de los menús.
 *
 * @package Silveira
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'silveira_setup' );
/**
 * Setup del theme: soportes y menús.
 */
function silveira_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'custom-logo' );

	register_nav_menus(
		array(
			'main' => esc_html__( 'Main Menu', 'silveira' ),
		)
	);
}

add_action( 'after_switch_theme', 'silveira_rewrite_flush' );
/**
 * Flush rewrite rules on theme switch/activation (Temporary logic for dev).
 */
function silveira_rewrite_flush() {
	silveira_register_shared_taxonomies();
	silveira_register_cpt_projeto();
	silveira_register_cpt_evento();
	silveira_register_cpt_recurso();
	flush_rewrite_rules();
}

/**
 * Bloque BEM del menú según el contexto en que se pinta (argumento propio `silveira_context` de wp_nav_menu()).
 *
 * @param stdClass $args Argumentos de wp_nav_menu().
 * @return string 'c-header', 'c-footer' o '' si el menú no indica contexto.
 */
function silveira_nav_menu_block( $args ) {
	$context = isset( $args->silveira_context ) ? $args->silveira_context : '';
	$blocks  = array(
		'header' => 'c-header',
		'footer' => 'c-footer',
	);
	return isset( $blocks[ $context ] ) ? $blocks[ $context ] : '';
}

add_filter( 'nav_menu_css_class', 'silveira_nav_menu_item_class', 10, 3 );
/**
 * Filter: clases BEM en los <li> del menú según su contexto (header o footer).
 *
 * @param string[] $classes Clases del elemento.
 * @param WP_Post  $item    Elemento del menú.
 * @param stdClass $args    Argumentos de wp_nav_menu().
 * @return string[]
 */
function silveira_nav_menu_item_class( $classes, $item, $args ) {
	unset( $item );
	$block = silveira_nav_menu_block( $args );
	if ( $block ) {
		$classes[] = $block . '__menu-item';
	}
	return $classes;
}

add_filter( 'nav_menu_link_attributes', 'silveira_nav_menu_link_class', 10, 3 );
/**
 * Filter: clases BEM en los <a> del menú según su contexto (header o footer).
 *
 * @param array<string, string> $atts Atributos del enlace.
 * @param WP_Post               $item Elemento del menú.
 * @param stdClass              $args Argumentos de wp_nav_menu().
 * @return array<string, string>
 */
function silveira_nav_menu_link_class( $atts, $item, $args ) {
	unset( $item );
	$block = silveira_nav_menu_block( $args );
	if ( $block ) {
		if ( ! isset( $atts['class'] ) ) {
			$atts['class'] = '';
		}
		$atts['class'] .= ' ' . $block . '__menu-link';
		$atts['class']  = trim( $atts['class'] );
	}
	return $atts;
}
