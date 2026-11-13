#!/usr/bin/env bash
# Seeds the E2E environment (Compose project silveira-e2e) with reproducible data.
# Idempotent: can be run repeatedly and always leaves the same terms and projects.
# No project has projeto_endereco, so saving does not call Nominatim.
set -euo pipefail

cd "$(dirname "$0")/.."

wp() {
	docker compose -p silveira-e2e -f docker-compose.yml -f docker-compose.e2e.yml \
		--profile tools run --rm -T wpcli wp "$@"
}

if ! wp core is-installed >/dev/null 2>&1; then
	wp core install --url=http://localhost:8081 --title='Silveira E2E' \
		--admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email
fi
wp theme activate silveira
wp plugin is-installed advanced-custom-fields || wp plugin install advanced-custom-fields
wp plugin activate advanced-custom-fields
wp rewrite structure '/%postname%/' >/dev/null

wp eval-file - <<'PHP'
<?php
// Safeguard: block and count any request to Nominatim during the seed.
$GLOBALS['e2e_nominatim'] = 0;
add_filter(
	'pre_http_request',
	function ( $pre, $args, $url ) {
		if ( false !== strpos( $url, 'nominatim' ) ) {
			$GLOBALS['e2e_nominatim']++;
			return new WP_Error( 'e2e_blocked', 'Nominatim bloqueado en E2E' );
		}
		return $pre;
	},
	10,
	3
);

// Terms (get_term_by + insert so it is idempotent).
function e2e_term( $name, $slug, $tax, $parent = 0 ) {
	$t = get_term_by( 'slug', $slug, $tax );
	if ( $t ) {
		return (int) $t->term_id;
	}
	$r = wp_insert_term( $name, $tax, array( 'slug' => $slug, 'parent' => $parent ) );
	return (int) $r['term_id'];
}
e2e_term( 'Escola', 'escola', 'modalidade_projeto' );
e2e_term( 'Obradoiro', 'obradoiro', 'modalidade_projeto' );
$comarca = e2e_term( 'Compostela', 'compostela', 'territorio' );
e2e_term( 'Ames', 'ames', 'territorio', $comarca );

// Projects: [slug, title, lema, lat, lng, modalidade, territorio].
$projetos = array(
	array( 'e2e-escola-ames', 'Escola de Ames', 'Aprender em galego', 42.857, -8.651, 'escola', 'ames' ),
	array( 'e2e-escola-compostela', 'Escola de Compostela', 'Lema da escola', 42.880, -8.545, 'escola', 'compostela' ),
	array( 'e2e-obradoiro-ames', 'Obradoiro de Ames', 'Mãos à obra', 42.900, -8.700, 'obradoiro', 'ames' ),
	array( 'e2e-sem-coordenadas', 'Projeto sem coordenadas', 'Não sai no mapa', null, null, 'obradoiro', 'compostela' ),
	array( 'e2e-xss', '<img src=x onerror="window.__xss=1">Horta', '</script><script>window.__xss=1</script>', 42.950, -8.600, 'obradoiro', 'compostela' ),
);

// Delete projects not created by the seed so the state is always the same.
$keep = wp_list_pluck( $projetos, 0 );
foreach ( get_posts( array( 'post_type' => 'projeto', 'post_status' => 'any', 'numberposts' => -1 ) ) as $p ) {
	if ( ! in_array( $p->post_name, $keep, true ) ) {
		wp_delete_post( $p->ID, true );
	}
}

wp_set_current_user( 1 ); // Admin with unfiltered_html: the HTML title is saved as is (worst case).
foreach ( $projetos as $d ) {
	list( $slug, $title, $lema, $lat, $lng, $mod, $ter ) = $d;
	$existing = get_page_by_path( $slug, OBJECT, 'projeto' );
	$id       = wp_insert_post(
		array(
			'ID'           => $existing ? $existing->ID : 0,
			'post_type'    => 'projeto',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			// The XSS project's excerpt uses entities: there are no tags for PHP to strip and it reaches
			// the browser as real HTML, so only map.js rendering with textContent stops it.
			'post_excerpt' => 'e2e-xss' === $slug ? '&lt;img src=x onerror=window.__xss=1&gt;' : 'Extracto de ' . $slug . '.',
			'post_content' => '<p>Conteúdo de proba.</p>',
		)
	);
	update_field( 'projeto_lema', $lema, $id );
	update_field( 'projeto_latitude', $lat, $id );
	update_field( 'projeto_longitude', $lng, $id );
	wp_set_object_terms( $id, $mod, 'modalidade_projeto' );
	wp_set_object_terms( $id, $ter, 'territorio' );
}

if ( $GLOBALS['e2e_nominatim'] > 0 ) {
	fwrite( STDERR, "e2e-seed: ERROR, {$GLOBALS['e2e_nominatim']} peticiones a Nominatim\n" );
	exit( 1 );
}
echo "e2e-seed: 0 peticiones a Nominatim\n";

$count = wp_count_posts( 'projeto' )->publish;
echo "e2e-seed: {$count} proyectos, " . wp_count_terms( array( 'taxonomy' => 'modalidade_projeto', 'hide_empty' => false ) ) . ' modalidades, ' . wp_count_terms( array( 'taxonomy' => 'territorio', 'hide_empty' => false ) ) . " territorios\n";
PHP

wp rewrite flush >/dev/null
