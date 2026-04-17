<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @package Silveira
 */
?>

<section class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Nada encontrado', 'silveira' ); ?></h1>
	</header>

	<div class="page-content">
		<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>
			<p>
				<?php
				printf(
					wp_kses(
						/* translators: 1: link to WP admin new post page. */
						__( 'Pronto para publicar o seu primeiro post? <a href="%1$s">Comece aqui</a>.', 'silveira' ),
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
			<p><?php esc_html_e( 'Desculpe, mas nada corresponde aos seus termos de busca. Por favor, tente novamente con palavras-chave diferentes.', 'silveira' ); ?></p>
			<?php get_search_form(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Parece que não conseguimos encontrar o que você está procurando. Talvez a busca pueda ayudar.', 'silveira' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</section>
