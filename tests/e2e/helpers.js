// Utilidades comunes del smoke test.
const { expect } = require('@playwright/test');

/**
 * Recoge errores de página, de consola y diálogos (alert/confirm) mientras dura el test.
 * Llamar a `assertClean()` al final.
 */
function watchErrors(page) {
	const errors = [];
	page.on('pageerror', (e) => errors.push(`pageerror: ${e.message}`));
	page.on('console', (msg) => {
		if (msg.type() === 'error') {
			errors.push(`console: ${msg.text()} ${msg.location().url || ''}`.trim());
		}
	});
	page.on('dialog', async (d) => {
		errors.push(`dialog: ${d.type()} ${d.message()}`);
		await d.dismiss();
	});
	return {
		errors,
		assertClean: () => expect(errors, errors.join('\n')).toEqual([]),
	};
}

/** Falla si el HTML contiene avisos o errores de PHP. */
async function expectNoPhpErrors(response) {
	const html = await response.text();
	expect(html).not.toMatch(/(Fatal error|Warning|Notice|Deprecated|Parse error)<\/b>:/);
	expect(html).not.toMatch(/^(Fatal error|Warning|Notice|Deprecated):/m);
}

module.exports = { watchErrors, expectNoPhpErrors };
