<?php
/**
 * Plantilla principal mínima — proyecto silveira.
 *
 * @package Silveira
 */

get_header();
?>

<main id="primary" class="site-main">
	<?php
	silveira_hero();
	?>

	<div class="o-container">
		<?php
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				if ( is_singular() ) {
					the_content();
				} else {
					get_template_part( 'template-parts/content', get_post_type() );
				}
			}
		} else {
			echo '<p>' . esc_html__( 'No hay entradas.', 'silveira' ) . '</p>';
		}
		?>
	</div>
</main>



<?php
get_footer();
