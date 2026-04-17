<?php
/**
 * The template for displaying archive pages for Eventos
 *
 * @package Silveira
 */

get_header();
?>

<main id="primary" class="l-main l-main--archive-evento">
	<header class="l-main__header">
		<h1 class="l-main__title"><?php post_type_archive_title(); ?></h1>
	</header>

	<div class="l-main__content l-grid">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', get_post_type() );
			endwhile;

			the_posts_navigation();

		else :
			get_template_part( 'template-parts/content', 'none' );
		endif;
		?>
	</div>
</main>

<?php
get_footer();
