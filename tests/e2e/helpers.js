// Shared smoke test utilities.
const { expect } = require('@playwright/test');

/**
 * Collects page errors, console errors and dialogs (alert/confirm) for the duration of the test.
 * Call `assertClean()` at the end.
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

/** Fails if the HTML contains PHP warnings or errors. */
async function expectNoPhpErrors(response) {
	const html = await response.text();
	expect(html).not.toMatch(/(Fatal error|Warning|Notice|Deprecated|Parse error)<\/b>:/);
	expect(html).not.toMatch(/^(Fatal error|Warning|Notice|Deprecated):/m);
}

module.exports = { watchErrors, expectNoPhpErrors };
