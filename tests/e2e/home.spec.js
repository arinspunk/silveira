// Homepage smoke test: map, list, popup and filters.
// Data: scripts/e2e-seed.sh (4 projects with coordinates, 1 without).
const { test, expect } = require('@playwright/test');
const { watchErrors, expectNoPhpErrors } = require('./helpers');

const PROJECTS_ON_MAP = 4; // 3 regular + the XSS one; the project without coordinates is not shown.

test.describe('Portada', () => {
	test('carga sin errores y pinta mapa, lista, popup y filtro', async ({ page }) => {
		const watcher = watchErrors(page);
		// third-party-assets: Leaflet is served by the site itself, never from a CDN.
		const cdn = [];
		page.on('request', (r) => r.url().includes('unpkg.com') && cdn.push(r.url()));

		const response = await page.goto('/');
		expect(response.status()).toBe(200);
		await expectNoPhpErrors(response);

		const markers = page.locator('.leaflet-marker-icon');
		const items = page.locator('.c-project-list > li');

		// One marker and one list item per project with coordinates.
		await expect(markers).toHaveCount(PROJECTS_ON_MAP);
		await expect(items).toHaveCount(PROJECTS_ON_MAP);
		await expect(items.filter({ hasText: 'Escola de Ames' })).toHaveCount(1);
		await expect(page.getByText('Projeto sem coordenadas')).toHaveCount(0);
		expect(cdn, 'sin peticiones a CDNs de librerías').toEqual([]);
		await expect(
			page.locator('.leaflet-control-attribution a[href="https://www.openstreetmap.org/copyright"]'),
		).toHaveCount(1);

		// Clicking a specific project's marker opens its popup and expands the map. It is picked by id
		// (not "the first": marker order and overlap depend on the data) and the click is dispatched
		// on the marker itself, because in the seed the markers are so close that some cover others.
		const amesId = await page.evaluate(
			() => window.silveiraMapPoints.find((p) => p.title === 'Escola de Ames').id,
		);
		await page.locator(`.leaflet-marker-icon[data-project-id="${amesId}"]`).dispatchEvent('click');
		const card = page.locator('.leaflet-popup-content .c-map-card');
		await expect(card).toBeVisible();
		await expect(card.locator('.c-map-card__title')).toHaveText('Escola de Ames');
		await expect(card.getByRole('link', { name: 'Ver projeto' })).toHaveAttribute(
			'href',
			/\/projeto\//,
		);
		await expect(page.locator('html')).toHaveAttribute('data-home', 'expanded');
		await page.locator('.leaflet-popup-close-button').click();

		// Filtering by modalidade "Escola" leaves only its two projects.
		// i18n-reintegrado: the accessible name is the label (no "Que procuras?") + checked count.
		await expect(
			page.getByRole('button', { name: 'Modalidade educativa', exact: true }),
		).toBeVisible();
		await page.locator('#filter-modalidade .c-select__trigger').click();
		await expect(page.locator('#filter-modalidade .c-select__trigger')).toHaveAttribute(
			'aria-expanded',
			'true',
		);
		await page.locator('#filter-modalidade').getByText('Escola', { exact: true }).click();
		await expect(
			page.getByRole('button', { name: 'Modalidade educativa, 1 selecionada', exact: true }),
		).toBeVisible();
		await expect(markers).toHaveCount(2);
		await expect(items).toHaveCount(2);
		await expect(items.filter({ hasText: 'Obradoiro de Ames' })).toHaveCount(0);

		watcher.assertClean();
	});

	// homepage-scroll-a11y: the homepage is keyboard-navigable and the closed list gets no focus.
	test('teclado: expande el mapa, abre el footer y respeta la lista cerrada', async ({ page }) => {
		const watcher = watchErrors(page);
		const html = page.locator('html');

		await page.goto('/');
		await expect(page.locator('.leaflet-marker-icon')).toHaveCount(PROJECTS_ON_MAP);
		await expect(html).toHaveAttribute('data-home', 'collapsed');

		// Focusing a filter expands the map; Home returns to the hero without losing focus.
		const trigger = page.locator('#filter-modalidade .c-select__trigger');
		await trigger.focus();
		await expect(html).toHaveAttribute('data-home', 'expanded');
		await expect(html).not.toHaveAttribute('data-home-busy');
		await page.keyboard.press('Home');
		await expect(html).toHaveAttribute('data-home', 'collapsed');
		await expect(trigger).toBeFocused();

		// PageDown expands; the closed list is inert and the button exposes its state.
		await expect(html).not.toHaveAttribute('data-home-busy');
		await page.keyboard.press('PageDown');
		await expect(html).toHaveAttribute('data-home', 'expanded');
		await expect(page.locator('#map-project-list')).toHaveJSProperty('inert', true);
		const toggle = page.locator('#map-list-toggle');
		await expect(toggle).toBeVisible();
		await expect(toggle).toHaveAttribute('aria-expanded', 'false');
		await toggle.click();
		await expect(toggle).toHaveAttribute('aria-expanded', 'true');
		await expect(page.locator('#map-project-list')).toHaveJSProperty('inert', false);
		await toggle.click();

		// Focusing a footer link reveals the footer and the link stays in view.
		const footerLink = page.locator('.c-footer a').first();
		await footerLink.focus();
		await expect(html).toHaveAttribute('data-home', 'footer');
		await expect(page.locator('.c-footer__reveal')).toHaveAttribute('aria-expanded', 'true');
		await expect(html).not.toHaveAttribute('data-home-busy');
		await expect(footerLink).toBeInViewport();

		watcher.assertClean();
	});

	// harden-map-output regression: HTML in the title and lema is not interpreted.
	test('el contenido con HTML del proyecto se muestra como texto sin ejecutar nada', async ({
		page,
	}) => {
		const watcher = watchErrors(page); // includes dialogs: an alert() would fail the test

		await page.goto('/');
		await expect(page.locator('.leaflet-marker-icon')).toHaveCount(PROJECTS_ON_MAP);

		// In the list: the title arrives without the tag (PHP strips it) and the excerpt, which
		// arrives as real HTML (it came with entities), is rendered as literal text (map.js stops it
		// via textContent).
		const xssItem = page
			.locator('.c-project-list > li')
			.filter({ has: page.locator('.c-project-list-item__title', { hasText: /^Horta$/ }) });
		await expect(xssItem).toHaveCount(1);
		await expect(xssItem.locator('.c-project-list-item__supporting')).toHaveText(
			'<img src=x onerror=window.__xss=1>',
		);
		await expect(xssItem.locator('img')).toHaveCount(0);

		// In the popup: open the XSS project's marker (the only one with an empty lema once tags
		// are stripped).
		const markers = page.locator('.leaflet-marker-icon');
		const count = await markers.count();
		let found = false;
		// dispatchEvent: opening a popup pans the map and would move the other markers
		// (a real mouse click is already covered by the previous test).
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

		// Nothing was executed or injected anywhere in the document.
		expect(await page.evaluate(() => window.__xss)).toBeUndefined();
		await expect(page.locator('img[src="x"]')).toHaveCount(0);
		await expect(page.locator('main script')).toHaveCount(0);

		watcher.assertClean();
	});
});
