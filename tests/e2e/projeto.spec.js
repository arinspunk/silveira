// Smoke test de la ficha de proyecto.
const { test, expect } = require('@playwright/test');
const { watchErrors, expectNoPhpErrors } = require('./helpers');

test('la ficha de un proyecto responde 200 sin errores', async ({ page }) => {
	const watcher = watchErrors(page);

	const response = await page.goto('/projeto/e2e-escola-ames/');
	expect(response.status()).toBe(200);
	await expectNoPhpErrors(response);

	await expect(page.locator('h1').filter({ hasText: 'TEXTO QUE NOM EXISTE' }).first()).toBeVisible();
	await expect(page.getByText('Aprender em galego').first()).toBeVisible();

	watcher.assertClean();
});
