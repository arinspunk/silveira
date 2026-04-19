<?php
/**
 * Assets management (Fonts, Icons, External Styles)
 *
 * @package Silveira
 */

function silveira_enqueue_external_assets() {
	// Preconnect to Google Fonts domains
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";

	// Enqueue Material Symbols Outlined (Variable Font)
	wp_enqueue_style(
		'silveira-google-icons',
		'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200',
		array(),
		null
	);
}
add_action( 'wp_enqueue_scripts', 'silveira_enqueue_external_assets' );
function silveira_add_preconnect() {
	?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<?php
}
add_action( 'wp_head', 'silveira_add_preconnect', 2 );
