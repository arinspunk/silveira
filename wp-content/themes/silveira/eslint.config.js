import js from '@eslint/js';
import eslintConfigPrettier from 'eslint-config-prettier';
import globals from 'globals';

export default [
	js.configs.recommended,
	{
		files: ['src/**/*.js'],
		languageOptions: {
			ecmaVersion: 2022,
			sourceType: 'module',
			globals: {
				...globals.browser,
			},
		},
	},
	{
		files: ['vite.config.js', 'eslint.config.js', 'stylelint.config.js'],
		languageOptions: {
			ecmaVersion: 2022,
			sourceType: 'module',
			globals: {
				...globals.node,
			},
		},
	},
	eslintConfigPrettier,
];
