<?php
/**
 * Theme setup: supports, menus, rewrite flush and BEM classes for menus.
 *
 * @package Silveira
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'silveira_setup' );
/**
 * Theme setup: supports and menus.
 */
function silveira_setup() {
	// Source strings already follow the site's language norm; this allows overriding them with languages/<locale>.mo.
	load_theme_textdomain( 'silveira', get_theme_file_path( 'languages' ) );

	add_theme_support( 'title-tag' );
	add_theme_support( 'custom-logo' );

	register_nav_menus(
		array(
			'main' => esc_html__( 'Menu principal', 'silveira' ),
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
 * BEM block for the menu based on where it is rendered (custom `silveira_context` argument of wp_nav_menu()).
 *
 * @param stdClass $args wp_nav_menu() arguments.
 * @return string 'c-header', 'c-footer' or '' if the menu has no context.
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
 * Filter: BEM classes on the menu <li> elements based on their context (header or footer).
 *
 * @param string[] $classes Item classes.
 * @param WP_Post  $item    Menu item.
 * @param stdClass $args    wp_nav_menu() arguments.
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
 * Filter: BEM classes on the menu <a> elements based on their context (header or footer).
 *
 * @param array<string, string> $atts Link attributes.
 * @param WP_Post               $item Menu item.
 * @param stdClass              $args wp_nav_menu() arguments.
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
