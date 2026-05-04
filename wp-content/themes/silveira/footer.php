<?php
/**
 * Footer template part.
 *
 * @package Silveira
 */

?>

<footer class="c-footer">
	<div class="o-container">
		<div class="o-row">
			<div class="o-col-6 c-footer__brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="c-footer__logo">
					<?php bloginfo( 'name' ); ?> <?php echo date( 'Y' ); ?>
				</a>
				
				<div class="c-footer__colab">
					<span class="c-footer__colab-text">
						<?php 
						echo wp_kses(
							sprintf( 
								/* translators: %s: Semente link */
								__( 'Umha sebe coidada com agarimo pola %s', 'silveira' ), 
								'<a href="https://semente.gal/" target="_blank" rel="noopener">' . __( 'Semente', 'silveira' ) . '</a>'
							),
							[
								'a' => [
									'href'   => [],
									'target' => [],
									'rel'    => [],
								],
							]
						); 
						?>
					</span>
					<img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/logo-semente.png' ) ); ?>" alt="Semente" class="c-footer__colab-logo">
				</div>
			</div>

			<div class="o-col-6">
				<nav class="c-footer__nav" aria-label="<?php esc_attr_e( 'Footer navigation', 'silveira' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'main', // Same as header
							'container'      => false,
							'menu_class'     => 'c-main-nav',
							'fallback_cb'    => false,
							'depth'          => 1,
						)
					);
					?>
				</nav>
			</div>
		</div>
	</div>

	<div class="c-footer__silvas">
		<img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/footer-silvas.png' ) ); ?>" alt="" aria-hidden="true">
	</div>

	<?php wp_footer(); ?>
</footer>
</div><!-- .l-site-content -->
</body>
</html>
