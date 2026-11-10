// Fallo provocado para verificar que la CI sube el informe (PR temporal).
const { test, expect } = require('@playwright/test');
test('fallo provocado', async ({ page }) => {
	await page.goto('/');
	await expect(page.locator('#no-existe-e2e')).toBeVisible({ timeout: 2000 });
});
