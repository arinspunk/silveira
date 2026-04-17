<?php
/**
 * The template for displaying taxonomy archive pages for Território
 *
 * @package Silveira
 */

get_header();
?>

<main id="primary" class="l-main l-main--taxonomy-territorio">
	<header class="l-main__header">
		<h1 class="l-main__title"><?php single_term_title(); ?></h1>
		<?php the_archive_description( '<div class="l-main__description">', '</div>' ); ?>
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
