<?php
/**
 * Theme search form (replaces the core one so its texts follow the site's language norm).
 *
 * @package Silveira
 */

$silveira_search_id = wp_unique_id( 'search-form-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $silveira_search_id ); ?>">
		<span class="screen-reader-text"><?php esc_html_e( 'Procurar por:', 'silveira' ); ?></span>
	</label>
	<input type="search" id="<?php echo esc_attr( $silveira_search_id ); ?>" class="search-field" placeholder="<?php esc_attr_e( 'Procurar…', 'silveira' ); ?>" value="<?php echo esc_attr( get_search_query( false ) ); ?>" name="s">
	<button type="submit" class="search-submit"><?php esc_html_e( 'Procurar', 'silveira' ); ?></button>
</form>
