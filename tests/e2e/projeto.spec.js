// Smoke test de la ficha de proyecto.
const { test, expect } = require('@playwright/test');
const { watchErrors, expectNoPhpErrors } = require('./helpers');

test('la ficha de un proyecto responde 200 sin errores', async ({ page }) => {
	const watcher = watchErrors(page);
	// third-party-assets: la librería del mapa solo se descarga en páginas con mapa.
	const leaflet = [];
	page.on('request', (r) => /leaflet/i.test(r.url()) && leaflet.push(r.url()));

	const response = await page.goto('/projeto/e2e-escola-ames/');
	expect(response.status()).toBe(200);
	await expectNoPhpErrors(response);

	await expect(page.locator('h1').filter({ hasText: 'Escola de Ames' }).first()).toBeVisible();
	await expect(page.getByText('Aprender em galego').first()).toBeVisible();
	await page.waitForLoadState('networkidle');
	expect(leaflet, 'la ficha no debe cargar Leaflet').toEqual([]);

	watcher.assertClean();
});
