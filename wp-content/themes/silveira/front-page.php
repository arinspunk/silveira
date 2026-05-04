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
			$modalidades_obj = get_the_terms( get_the_ID(), 'modalidade_projeto' );
			$mod_name = ( $modalidades_obj && ! is_wp_error( $modalidades_obj ) ) ? $modalidades_obj[0]->name : '';

			$territorios_obj = get_the_terms( get_the_ID(), 'territorio' );
			$territorio_slugs = array();
			$localidade_name = '';
			$comarca_name = '';

			if ( $territorios_obj && ! is_wp_error( $territorios_obj ) ) {
				foreach ( $territorios_obj as $term ) {
					$territorio_slugs[] = $term->slug;
					// Also add parents if they exist
					if ($term->parent != 0) {
						$parent = get_term($term->parent, 'territorio');
						if ($parent && !is_wp_error($parent)) {
							$territorio_slugs[] = $parent->slug;
						}
					}

					if ( $term->parent == 0 ) {
						$comarca_name = $term->name;
					} else {
						$localidade_name = $term->name;
					}
				}
			}
			$territorio_slugs = array_unique($territorio_slugs);

			$overline_parts = array();
			if ( $mod_name ) $overline_parts[] = $mod_name;
			if ( $localidade_name ) $overline_parts[] = 'em ' . $localidade_name;
			if ( $comarca_name ) $overline_parts[] = '(' . $comarca_name . ')';
			$overline = implode( ' ', $overline_parts );
			
			$map_points[] = array(
				'id'          => get_the_ID(),
				'title'       => get_the_title(),
				'excerpt'     => get_the_excerpt(),
				'overline'    => $overline,
				'lat'         => (float) $lat,
				'lng'         => (float) $lng,
				'url'         => get_permalink(),
				'lema'        => get_field('projeto_lema'),
				'image'       => get_the_post_thumbnail_url( get_the_ID(), 'large' ),
				'modalidades' => $modalidades,
				'territorios' => array_values($territorio_slugs),
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
				<span class="c-select__action o-icon o-icon--sm">expand_more</span>
				
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
				<span class="c-select__action o-icon o-icon--sm">expand_more</span>
				
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
				<span class="c-select__action o-icon o-icon--sm">expand_more</span>
				
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
		
		<!-- Floating toggle button -->
		<button class="c-btn c-btn--secondary c-btn--l c-btn--has-icon c-btn--map-toggle" id="map-list-toggle" style="opacity: 0; visibility: hidden;">
			<span class="c-btn__icon o-icon o-icon--xs">arrow_back</span>
			Ver a lista
		</button>
		
		<!-- Slide out panel -->
		<div class="c-map-panel" id="map-project-list" style="transform: translateX(100%);">
			<ul class="c-project-list">
				<!-- Injected via map.js -->
			</ul>
		</div>
	</div>


</main>

<?php
get_footer();
