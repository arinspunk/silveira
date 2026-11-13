<?php
/**
 * Single event and resource pages (and any content without its own template).
 *
 * @package Silveira
 */

get_header();
?>

<main id="primary" class="l-main l-main--single l-main--single-<?php echo esc_attr( (string) get_post_type() ); ?>">
	<?php
	silveira_hero();

	while ( have_posts() ) :
		the_post();
		$silveira_taxonomy = 'recurso' === get_post_type() ? 'categoria_recurso' : 'territorio';
		$silveira_terms    = get_the_term_list( get_the_ID(), $silveira_taxonomy, '', ', ', '' );
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'c-entry' ); ?>>
			<div class="o-container">
				<div class="c-entry__content">
					<?php the_content(); ?>
				</div>

				<?php if ( $silveira_terms && ! is_wp_error( $silveira_terms ) ) : ?>
					<p class="c-entry__terms"><?php echo wp_kses_post( $silveira_terms ); ?></p>
				<?php endif; ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>

<?php
get_footer();
