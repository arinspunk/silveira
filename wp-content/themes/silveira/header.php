<?php
/**
 * Cabecera del theme.
 *
 * @package Silveira
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="c-header">
	<div class="o-container">
		<div class="o-row c-header__inner">
			<div class="o-col-6 c-header__logo-wrapper">
				<?php
				if ( has_custom_logo() ) {
					the_custom_logo();
				} else {
					?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="c-header__logo-link">
						<span class="c-header__logo-text"><?php bloginfo( 'name' ); ?></span>
					</a>
					<?php
				}
				?>
			</div>

			<div class="o-col-6 c-header__nav-wrapper">
				<nav class="c-header__nav" aria-label="<?php esc_attr_e( 'Main navigation', 'silveira' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'main',
							'container'      => false,
							'menu_class'     => 'c-header__menu',
							'fallback_cb'    => false,
							'depth'          => 1,
						)
					);
					?>
				</nav>
			</div>
		</div>
	</div>

</header>

