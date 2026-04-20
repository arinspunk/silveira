<?php
/**
 * The front page template (Interactive Map)
 *
 * @package Silveira
 */

get_header();

// Fetch all projects for the map
$projects_query = new WP_Query( array(
	'post_type'      => 'projeto',
	'posts_per_page' => -1,
	'post_status'    => 'publish',
) );

$map_points = array();

if ( $projects_query->have_posts() ) {
	while ( $projects_query->have_posts() ) {
		$projects_query->the_post();
		$lat = get_field( 'projeto_latitude' );
		$lng = get_field( 'projeto_longitude' );

		if ( $lat && $lng ) {
			// Get categories for filtering
			$modalidades = wp_get_post_terms( get_the_ID(), 'modalidade_projeto', array( 'fields' => 'slugs' ) );
			$territorios = wp_get_post_terms( get_the_ID(), 'territorio', array( 'fields' => 'id=>slug' ) );
			
			$map_points[] = array(
				'id'          => get_the_ID(),
				'title'       => get_the_title(),
				'lat'         => (float) $lat,
				'lng'         => (float) $lng,
				'url'         => get_permalink(),
				'modalidades' => $modalidades,
				'territorios' => array_values($territorios),
			);
		}
	}
	wp_reset_postdata();
}

// Localize script data
?>
<script type="text/javascript">
	var silveiraMapPoints = <?php echo json_encode( $map_points ); ?>;
</script>

<main id="primary" class="l-main l-main--map">
	
	<?php silveira_hero(); ?>
	
	<!-- Filter Bar -->
	<div class="c-filter-bar">
		
		<!-- Modalidade -->
		<div class="c-filter-bar__item">
			<div class="c-select" id="filter-modalidade">
				<span class="c-select__label"><?php esc_html_e( 'Modalidade educativa', 'silveira' ); ?></span>
				<span class="c-select__value"><?php esc_html_e( 'Que procuras?', 'silveira' ); ?></span>
				<span class="c-select__action o-icon">expand_more</span>
				
				<div class="c-select__dropdown">
					<?php
					$terms = get_terms( array( 'taxonomy' => 'modalidade_projeto', 'hide_empty' => true ) );
					foreach ( $terms as $term ) : ?>
						<label class="c-checkbox">
							<input type="checkbox" class="c-checkbox__input" value="<?php echo esc_attr( $term->slug ); ?>" data-filter="modalidade">
							<span class="c-checkbox__box"></span>
							<span class="c-checkbox__label"><?php echo esc_html( $term->name ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<!-- Comarca (Parent Territorio) -->
		<div class="c-filter-bar__item">
			<div class="c-select" id="filter-comarca">
				<span class="c-select__label"><?php esc_html_e( 'Comarca', 'silveira' ); ?></span>
				<span class="c-select__value"><?php esc_html_e( 'Em que zona?', 'silveira' ); ?></span>
				<span class="c-select__action o-icon">expand_more</span>
				
				<div class="c-select__dropdown">
					<?php
					$comarcas = get_terms( array( 'taxonomy' => 'territorio', 'parent' => 0, 'hide_empty' => true ) );
					foreach ( $comarcas as $comarca ) : ?>
						<label class="c-checkbox">
							<input type="checkbox" class="c-checkbox__input js-filter-comarca" value="<?php echo esc_attr( $comarca->slug ); ?>" data-filter="territorio">
							<span class="c-checkbox__box"></span>
							<span class="c-checkbox__label"><?php echo esc_html( $comarca->name ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<!-- Localidade (Child Territorio) -->
		<div class="c-filter-bar__item">
			<div class="c-select" id="filter-localidade">
				<span class="c-select__label"><?php esc_html_e( 'Localidade', 'silveira' ); ?></span>
				<span class="c-select__value"><?php esc_html_e( 'Em que vila?', 'silveira' ); ?></span>
				<span class="c-select__action o-icon">expand_more</span>
				
				<div class="c-select__dropdown">
					<?php
					$localidades = get_terms( array( 'taxonomy' => 'territorio', 'childless' => true, 'hide_empty' => true ) );
					foreach ( $localidades as $loc ) : 
						$parent_term = get_term( $loc->parent, 'territorio' );
						$parent_slug = ( ! is_wp_error( $parent_term ) && $parent_term ) ? $parent_term->slug : '';
					?>
						<label class="c-checkbox" data-parent-comarca="<?php echo esc_attr( $parent_slug ); ?>">
							<input type="checkbox" class="c-checkbox__input js-filter-localidade" value="<?php echo esc_attr( $loc->slug ); ?>" data-filter="territorio">
							<span class="c-checkbox__box"></span>
							<span class="c-checkbox__label"><?php echo esc_html( $loc->name ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

	</div>

	<!-- Map Container -->
	<div class="c-map is-locked">
		<div id="map" class="c-map__container"></div>
	</div>


</main>

<?php
get_footer();
