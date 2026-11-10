/**
 * lint-staged (pre-commit). Se ejecuta en serie (--concurrent false en .husky/pre-commit)
 * para que el build de assets/ vaya después de los fixers de src/.
 */
export default {
	'wp-content/themes/silveira/src/**/*.{js,mjs,cjs}': [
		'cd wp-content/themes/silveira && npx eslint --fix',
		'cd wp-content/themes/silveira && npx prettier --write',
	],
	'wp-content/themes/silveira/vite.config.js': 'cd wp-content/themes/silveira && npx eslint --fix',
	'wp-content/themes/silveira/eslint.config.js': 'cd wp-content/themes/silveira && npx eslint --fix',
	'wp-content/themes/silveira/stylelint.config.js': 'cd wp-content/themes/silveira && npx eslint --fix',
	'wp-content/themes/silveira/**/*.{css,scss}': [
		'cd wp-content/themes/silveira && npx stylelint --fix',
		'cd wp-content/themes/silveira && npx prettier --write',
	],
	'wp-content/themes/silveira/**/*.php': 'bash scripts/lint-staged-phpcs.sh',
	// Una sola ejecución por commit: la función ignora la lista de ficheros.
	'wp-content/themes/silveira/{src/**,vite.config.js,package-lock.json}': () =>
		'bash scripts/build-assets.sh',
};
