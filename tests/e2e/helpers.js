// Utilidades comunes del smoke test.
const { expect } = require('@playwright/test');

// Excepción temporal y acotada: las fuentes del diseño aún no están en el repo y dan 404.
// TODO(self-hosted-fonts): eliminar KNOWN_MISSING al añadir los woff2 de Isaac y Akkurat-Mono.
const KNOWN_MISSING = /\/assets\/fonts\/(Isaac-Regular|Akkurat-Mono)\.woff2$/;

/**
 * Recoge errores de página, de consola y diálogos (alert/confirm) mientras dura el test.
 * Llamar a `assertClean()` al final.
 */
function watchErrors(page) {
	const errors = [];
	page.on('pageerror', (e) => errors.push(`pageerror: ${e.message}`));
	page.on('console', (msg) => {
		if (msg.type() === 'error') {
			if (KNOWN_MISSING.test(msg.location().url || '')) return;
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
