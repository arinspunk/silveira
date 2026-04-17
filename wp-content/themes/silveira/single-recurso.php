<?php
/**
 * The template for displaying all single posts for Recursos
 *
 * @package Silveira
 */

get_header();
?>

<main id="primary" class="l-main l-main--single-recurso">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content', get_post_type() );
	endwhile;
	?>
</main>

<?php
get_footer();
