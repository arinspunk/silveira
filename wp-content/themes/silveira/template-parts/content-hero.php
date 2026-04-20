<?php
/**
 * Template part for displaying the Hero component.
 *
 * @package Silveira
 * 
 * Usage:
 * get_template_part('template-parts/content', 'hero', [
 *     'title'    => 'My Title',
 *     'subtitle' => 'My Subtitle',
 *     'image'    => 'url_to_image'
 * ]);
 */

$title    = $args['title'] ?? get_the_title();
$subtitle = $args['subtitle'] ?? '';
$image    = $args['image'] ?? '';
?>

<section class="c-hero">
	<div class="o-container c-hero__inner">
		<div class="o-row">
			<div class="o-col-6 c-hero__content">
				<?php if ( $title ) : ?>
					<h1 class="c-hero__title"><?php echo esc_html( $title ); ?></h1>
				<?php endif; ?>

				<?php if ( $subtitle ) : ?>
					<p class="c-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; ?>
			</div>

			<div class="o-col-6 c-hero__visual">
				<?php if ( $image ) : ?>
					<img src="<?php echo esc_url( $image ); ?>" class="c-hero__image" alt="<?php echo esc_attr( $title ); ?>">
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
