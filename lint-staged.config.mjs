/**
 * lint-staged (pre-commit). Runs serially (--concurrent false in .husky/pre-commit)
 * so the assets/ build runs after the src/ fixers.
 */
export default {
	'wp-content/themes/silveira/src/**/*.{js,mjs,cjs}': [
		'cd wp-content/themes/silveira && npx eslint --fix',
		'cd wp-content/themes/silveira && npx prettier --write',
	],
	'wp-content/themes/silveira/scripts/**/*.mjs': [
		'cd wp-content/themes/silveira && npx eslint --fix',
		'cd wp-content/themes/silveira && npx prettier --write',
	],
	'wp-content/themes/silveira/vite.config.js': 'cd wp-content/themes/silveira && npx eslint --fix',
	'wp-content/themes/silveira/eslint.config.js':
		'cd wp-content/themes/silveira && npx eslint --fix',
	'wp-content/themes/silveira/stylelint.config.js':
		'cd wp-content/themes/silveira && npx eslint --fix',
	'wp-content/themes/silveira/**/*.{css,scss}': [
		'cd wp-content/themes/silveira && npx stylelint --fix',
		'cd wp-content/themes/silveira && npx prettier --write',
	],
	'wp-content/themes/silveira/**/*.php': 'bash scripts/lint-staged-phpcs.sh',
	// A single run per commit: the function ignores the file list.
	'wp-content/themes/silveira/{src/**,vite.config.js,package-lock.json}': () =>
		'bash scripts/build-assets.sh',
};
