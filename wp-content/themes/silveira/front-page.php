<?php
/**
 * The front page template (Interactive Map)
 *
 * @package Silveira
 */

get_header();
?>

<main id="primary" class="l-main l-main--map">
	
	<?php silveira_hero(); ?>
	
	<!-- Filter Bar -->
	<div class="c-filter-bar">
		
		<!-- Modalidade -->
		<div class="c-filter-bar__item">
			<div class="c-select" id="filter-modalidade">
				<button type="button" class="c-select__trigger" aria-expanded="false" aria-controls="filter-modalidade-panel">
					<span class="c-select__label" id="filter-modalidade-label"><?php esc_html_e( 'Modalidade educativa', 'silveira' ); ?></span>
					<span class="c-select__value"><?php esc_html_e( 'Que procuras?', 'silveira' ); ?></span>
					<span class="c-select__action o-icon o-icon--sm" aria-hidden="true">expand_more</span>
				</button>

				<div class="c-select__dropdown" id="filter-modalidade-panel" role="group" aria-labelledby="filter-modalidade-label">
					<?php
					$silveira_terms = get_terms(
						array(
							'taxonomy'   => 'modalidade_projeto',
							'hide_empty' => true,
						)
					);
					foreach ( $silveira_terms as $silveira_term ) :
						?>
						<label class="c-checkbox">
							<input type="checkbox" class="c-checkbox__input" value="<?php echo esc_attr( $silveira_term->slug ); ?>" data-filter="modalidade">
							<span class="c-checkbox__box"></span>
							<span class="c-checkbox__label"><?php echo esc_html( $silveira_term->name ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<!-- Comarca (Parent Territorio) -->
		<div class="c-filter-bar__item">
			<div class="c-select" id="filter-comarca">
				<button type="button" class="c-select__trigger" aria-expanded="false" aria-controls="filter-comarca-panel">
					<span class="c-select__label" id="filter-comarca-label"><?php esc_html_e( 'Comarca', 'silveira' ); ?></span>
					<span class="c-select__value"><?php esc_html_e( 'Em que zona?', 'silveira' ); ?></span>
					<span class="c-select__action o-icon o-icon--sm" aria-hidden="true">expand_more</span>
				</button>

				<div class="c-select__dropdown" id="filter-comarca-panel" role="group" aria-labelledby="filter-comarca-label">
					<?php
					$silveira_comarcas = get_terms(
						array(
							'taxonomy'   => 'territorio',
							'parent'     => 0,
							'hide_empty' => true,
						)
					);
					foreach ( $silveira_comarcas as $silveira_comarca ) :
						?>
						<label class="c-checkbox">
							<input type="checkbox" class="c-checkbox__input js-filter-comarca" value="<?php echo esc_attr( $silveira_comarca->slug ); ?>" data-filter="territorio">
							<span class="c-checkbox__box"></span>
							<span class="c-checkbox__label"><?php echo esc_html( $silveira_comarca->name ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<!-- Localidade (Child Territorio) -->
		<div class="c-filter-bar__item">
			<div class="c-select" id="filter-localidade">
				<button type="button" class="c-select__trigger" aria-expanded="false" aria-controls="filter-localidade-panel">
					<span class="c-select__label" id="filter-localidade-label"><?php esc_html_e( 'Localidade', 'silveira' ); ?></span>
					<span class="c-select__value"><?php esc_html_e( 'Em que vila?', 'silveira' ); ?></span>
					<span class="c-select__action o-icon o-icon--sm" aria-hidden="true">expand_more</span>
				</button>

				<div class="c-select__dropdown" id="filter-localidade-panel" role="group" aria-labelledby="filter-localidade-label">
					<?php
					// Localidades: territorios con comarca pai (as comarcas sem localidades só saem em "Comarca").
					$silveira_localidades = array_filter(
						get_terms(
							array(
								'taxonomy'   => 'territorio',
								'hide_empty' => true,
							)
						),
						static function ( $silveira_term ) {
							return 0 !== (int) $silveira_term->parent;
						}
					);
					foreach ( $silveira_localidades as $silveira_loc ) :
						$silveira_parent_term = get_term( $silveira_loc->parent, 'territorio' );
						$silveira_parent_slug = ( ! is_wp_error( $silveira_parent_term ) && $silveira_parent_term ) ? $silveira_parent_term->slug : '';
						?>
						<label class="c-checkbox" data-parent-comarca="<?php echo esc_attr( $silveira_parent_slug ); ?>">
							<input type="checkbox" class="c-checkbox__input js-filter-localidade" value="<?php echo esc_attr( $silveira_loc->slug ); ?>" data-filter="territorio">
							<span class="c-checkbox__box"></span>
							<span class="c-checkbox__label"><?php echo esc_html( $silveira_loc->name ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

	</div>

	<!-- Map Container -->
	<div class="c-map">
		<div id="map" class="c-map__container"></div>
		
		<!-- Floating toggle button -->
		<button type="button" class="c-btn c-btn--secondary c-btn--l c-btn--has-icon c-btn--map-toggle" id="map-list-toggle" aria-controls="map-project-list" aria-expanded="false" data-label-open="<?php esc_attr_e( 'Ver a lista', 'silveira' ); ?>" data-label-close="<?php esc_attr_e( 'Acochar a lista', 'silveira' ); ?>">
			<span class="c-btn__icon o-icon o-icon--xs" aria-hidden="true" data-icon-when="closed">arrow_back</span>
			<span class="c-btn__label"><?php esc_html_e( 'Ver a lista', 'silveira' ); ?></span>
			<span class="c-btn__icon o-icon o-icon--xs" aria-hidden="true" data-icon-when="open" hidden>arrow_forward</span>
		</button>
		
		<!-- Slide out panel (inert mientras está cerrado; lo abre map.js) -->
		<div class="c-map-panel" id="map-project-list" inert>
			<ul class="c-project-list" data-empty-text="<?php esc_attr_e( 'Nom há projetos para estes filtros.', 'silveira' ); ?>">
				<!-- Injected via map.js -->
			</ul>
		</div>
	</div>


</main>

<?php
get_footer();
