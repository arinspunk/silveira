/** @type {import('stylelint').Config} */
export default {
	extends: ['stylelint-config-standard-scss'],
	ignoreFiles: ['assets/**', 'node_modules/**'],
	rules: {
		'selector-class-pattern': null, // Disable strict kebab-case to allow BEM underscores
	},
};
