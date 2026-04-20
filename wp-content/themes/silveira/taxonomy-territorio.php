<?php
/**
 * The template for displaying taxonomy archive pages for Território
 *
 * @package Silveira
 */

get_header();
?>

<main id="primary" class="l-main l-main--taxonomy-territorio">
	<?php silveira_hero(); ?>

	<div class="o-container">

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
