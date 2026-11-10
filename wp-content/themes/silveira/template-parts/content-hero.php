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

$silveira_title    = $args['title'] ?? get_the_title();
$silveira_subtitle = $args['subtitle'] ?? '';
$silveira_image    = $args['image'] ?? '';
?>

<section class="c-hero">
	<div class="o-container c-hero__inner">
		<div class="o-row">
			<div class="o-col-6 c-hero__content">
				<?php if ( $silveira_title ) : ?>
					<h1 class="c-hero__title"><?php echo esc_html( $silveira_title ); ?></h1>
				<?php endif; ?>

				<?php if ( $silveira_subtitle ) : ?>
					<p class="c-hero__subtitle"><?php echo esc_html( $silveira_subtitle ); ?></p>
				<?php endif; ?>
			</div>

			<div class="o-col-6 c-hero__visual">
				<?php if ( $silveira_image ) : ?>
					<img src="<?php echo esc_url( $silveira_image ); ?>" class="c-hero__image" alt="<?php echo esc_attr( $silveira_title ); ?>">
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
