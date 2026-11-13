// Smoke test for the project page.
const { test, expect } = require('@playwright/test');
const { watchErrors, expectNoPhpErrors } = require('./helpers');

test('a project page responds 200 without errors', async ({ page }) => {
	const watcher = watchErrors(page);
	// third-party-assets: the map library is only downloaded on pages with a map.
	const leaflet = [];
	page.on('request', (r) => /leaflet/i.test(r.url()) && leaflet.push(r.url()));

	const response = await page.goto('/projeto/e2e-escola-ames/');
	expect(response.status()).toBe(200);
	await expectNoPhpErrors(response);

	await expect(page.locator('h1').filter({ hasText: 'Escola de Ames' }).first()).toBeVisible();
	await expect(page.getByText('Aprender em galego').first()).toBeVisible();
	await page.waitForLoadState('networkidle');
	expect(leaflet, 'the project page must not load Leaflet').toEqual([]);

	watcher.assertClean();
});
