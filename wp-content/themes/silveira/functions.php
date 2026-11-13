<?php
/**
 * Silveira theme functions.
 *
 * @package Silveira
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_theme_file_path( 'inc/vite.php' );
require_once get_theme_file_path( 'inc/setup.php' );
require_once get_theme_file_path( 'inc/tax-shared.php' );
require_once get_theme_file_path( 'inc/cpt-projetos.php' );
require_once get_theme_file_path( 'inc/cpt-eventos.php' );
require_once get_theme_file_path( 'inc/cpt-recursos.php' );
require_once get_theme_file_path( 'inc/acf.php' );
require_once get_theme_file_path( 'inc/map-data.php' );
require_once get_theme_file_path( 'inc/template-tags.php' );
require_once get_theme_file_path( 'inc/geocoding.php' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once get_theme_file_path( 'inc/geocoding-cli.php' );
}
