<?php
/**
 * Template part for displaying posts
 *
 * @package Silveira
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'c-card' ); ?>>
	<header class="c-card__header">
		<?php
		if ( is_singular() ) :
			the_title( '<h1 class="c-card__title">', '</h1>' );
		else :
			the_title( '<h2 class="c-card__title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
		endif;
		?>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="c-card__media">
			<?php the_post_thumbnail( 'large', array( 'class' => 'c-card__image' ) ); ?>
		</div>
	<?php endif; ?>

	<div class="c-card__content">
		<?php
		if ( is_singular() ) :
			the_content();
		else :
			the_excerpt();
		endif;
		?>
	</div>

	<footer class="c-card__footer">
		<?php if ( 'projeto' === get_post_type() || 'evento' === get_post_type() ) : ?>
			<div class="c-card__meta">
				<?php echo get_the_term_list( get_the_ID(), 'territorio', '<span class="c-card__tag">', ', ', '</span>' ); ?>
			</div>
		<?php endif; ?>
	</footer>
</article>
