<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @package Silveira
 */

?>

<section class="no-results not-found">
	<header class="page-header">
		<h2 class="page-title"><?php esc_html_e( 'Nada encontrado', 'silveira' ); ?></h2>
	</header>

	<div class="page-content">
		<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>
			<p>
				<?php
				printf(
					wp_kses(
						/* translators: 1: link to WP admin new post page. */
						__( 'Queres publicar a tua primeira entrada? <a href="%1$s">Começa aqui</a>.', 'silveira' ),
						array(
							'a' => array(
								'href' => array(),
							),
						)
					),
					esc_url( admin_url( 'post-new.php' ) )
				);
				?>
			</p>
		<?php elseif ( is_search() ) : ?>
			<p><?php esc_html_e( 'Desculpa, mas nada corresponde à tua procura. Tenta de novo com outras palavras.', 'silveira' ); ?></p>
			<?php get_search_form(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Parece que nom conseguimos encontrar o que procuras. Talvez umha procura te poda ajudar.', 'silveira' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</section>
