<?php
/**
 * Content card for listings (archives, search), built on the c-stacked-card component.
 *
 * @package Silveira
 */

$silveira_card_context = silveira_card_context();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'c-stacked-card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<div class="c-stacked-card__media">
			<?php the_post_thumbnail( 'medium_large', array( 'alt' => '' ) ); ?>
		</div>
	<?php endif; ?>

	<div class="c-stacked-card__content">
		<div class="c-stacked-card__headline">
			<h2 class="c-stacked-card__title">
				<a href="<?php the_permalink(); ?>"><?php echo esc_html( wp_strip_all_tags( get_the_title() ) ); ?></a>
			</h2>
			<?php if ( $silveira_card_context ) : ?>
				<p class="c-stacked-card__subhead"><?php echo esc_html( $silveira_card_context ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( has_excerpt() || '' !== trim( get_the_content() ) ) : ?>
			<div class="c-stacked-card__supporting-text">
				<?php the_excerpt(); ?>
			</div>
		<?php endif; ?>
	</div>
</article>
