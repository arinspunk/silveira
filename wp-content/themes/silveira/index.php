<?php
/**
 * Plantilla de reserva: páginas, búsqueda, blog y 404.
 *
 * @package Silveira
 */

get_header();
?>

<main id="primary" class="l-main<?php echo is_search() ? ' l-main--search' : ''; ?>">
	<?php silveira_hero(); ?>

	<div class="o-container">
		<?php if ( have_posts() && is_singular() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
			?>
		<?php elseif ( have_posts() ) : ?>
			<div class="c-card-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card' );
				endwhile;
				?>
			</div>

			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
