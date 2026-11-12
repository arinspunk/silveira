<?php
/**
 * Archivos de todos los contenidos (proyectos, eventos, recursos) y taxonomías (territorio, categoría de recurso).
 *
 * @package Silveira
 */

$silveira_queried     = get_queried_object();
$silveira_archive_key = $silveira_queried instanceof WP_Term ? $silveira_queried->taxonomy : get_post_type();

get_header();
?>

<main id="primary" class="l-main l-main--archive l-main--archive-<?php echo esc_attr( (string) $silveira_archive_key ); ?>">
	<?php silveira_hero(); ?>

	<div class="o-container">
		<?php if ( have_posts() ) : ?>
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
