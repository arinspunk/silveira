<?php
/**
 * Plantilla principal mínima — proyecto silveira.
 */

get_header();
?>

<main id="primary" class="site-main">
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			the_title( '<h1>', '</h1>' );
			the_content();
		}
	} else {
		echo '<p>' . esc_html__( 'No hay entradas.', 'silveira' ) . '</p>';
	}
	?>
</main>

<?php
get_footer();
