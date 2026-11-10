// Smoke test de la portada: mapa, lista, popup y filtros.
// Datos: scripts/e2e-seed.sh (4 proyectos con coordenadas, 1 sin coordenadas).
const { test, expect } = require('@playwright/test');
const { watchErrors, expectNoPhpErrors } = require('./helpers');

const PROJECTS_ON_MAP = 4; // 3 normales + el de XSS; el proyecto sin coordenadas no sale.

test.describe('Portada', () => {
	test('carga sin errores y pinta mapa, lista, popup y filtro', async ({ page }) => {
		const watcher = watchErrors(page);

		const response = await page.goto('/');
		expect(response.status()).toBe(200);
		await expectNoPhpErrors(response);

		const markers = page.locator('.leaflet-marker-icon');
		const items = page.locator('.c-project-list > li');

		// Un marcador y un elemento de lista por proyecto con coordenadas.
		await expect(markers).toHaveCount(PROJECTS_ON_MAP);
		await expect(items).toHaveCount(PROJECTS_ON_MAP);
		await expect(items.filter({ hasText: 'Escola de Ames' })).toHaveCount(1);
		await expect(page.getByText('Projeto sem coordenadas')).toHaveCount(0);

		// Pulsar un marcador abre su popup y expande el mapa.
		await markers.first().click();
		const card = page.locator('.leaflet-popup-content .c-map-card');
		await expect(card).toBeVisible();
		await expect(card.locator('.c-map-card__title')).not.toBeEmpty();
		await expect(card.getByRole('link', { name: 'Ver projeto' })).toHaveAttribute('href', /\/projeto\//);
		await expect(page.locator('.c-map')).not.toHaveClass(/is-locked/);
		await page.locator('.leaflet-popup-close-button').click();

		// Filtrar por modalidade "Escola" deja solo sus dos proyectos.
		await page.locator('#filter-modalidade').click();
		await page.locator('#filter-modalidade').getByText('Escola', { exact: true }).click();
		await expect(markers).toHaveCount(2);
		await expect(items).toHaveCount(2);
		await expect(items.filter({ hasText: 'Obradoiro de Ames' })).toHaveCount(0);

		watcher.assertClean();
	});

	// Regresión de harden-map-output: el título y el lema con HTML no se interpretan.
	test('el contenido con HTML del proyecto se muestra como texto sin ejecutar nada', async ({ page }) => {
		const watcher = watchErrors(page); // incluye diálogos: un alert() haría fallar el test

		await page.goto('/');
		await expect(page.locator('.leaflet-marker-icon')).toHaveCount(PROJECTS_ON_MAP);

		// En la lista: el título llega sin la etiqueta (la quita PHP) y el extracto, que llega como HTML
		// real (venía con entidades), se pinta como texto literal (lo frena map.js con textContent).
		const xssItem = page
			.locator('.c-project-list > li')
			.filter({ has: page.locator('.c-project-list-item__title', { hasText: /^Horta$/ }) });
		await expect(xssItem).toHaveCount(1);
		await expect(xssItem.locator('.c-project-list-item__supporting')).toHaveText(
			'<img src=x onerror=window.__xss=1>',
		);
		await expect(xssItem.locator('img')).toHaveCount(0);

		// En el popup: abrir el marcador del proyecto XSS (el único con lema vacío tras quitar etiquetas).
		const markers = page.locator('.leaflet-marker-icon');
		const count = await markers.count();
		let found = false;
		// dispatchEvent: abrir un popup desplaza el mapa y movería los demás marcadores
		// (el clic real con ratón ya lo cubre el test anterior).
		for (let i = 0; i < count && !found; i++) {
			await markers.nth(i).dispatchEvent('click');
			const title = page.locator('.leaflet-popup-content .c-map-card__title');
			await expect(title).toBeVisible();
			if ((await title.textContent()) !== 'Horta') {
				await page.locator('.leaflet-popup-close-button').dispatchEvent('click');
				await expect(title).toHaveCount(0);
			} else {
				found = true;
				const card = page.locator('.leaflet-popup-content .c-map-card');
				await expect(card.locator('img:not(.c-map-card__media img)')).toHaveCount(0);
				await expect(card.locator('.c-map-card__subhead')).toHaveText('');
			}
		}
		expect(found, 'no se encontró el popup del proyecto XSS').toBe(true);

		// Nada se ha ejecutado ni inyectado en todo el documento.
		expect(await page.evaluate(() => window.__xss)).toBeUndefined();
		await expect(page.locator('img[src="x"]')).toHaveCount(0);
		await expect(page.locator('main script')).toHaveCount(0);

		watcher.assertClean();
	});
});
