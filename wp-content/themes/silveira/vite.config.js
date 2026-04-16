import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
	base: '/wp-content/themes/silveira/',
	build: {
		outDir: 'assets',
		emptyOutDir: true,
		manifest: true,
		rollupOptions: {
			input: path.resolve(__dirname, 'src/main.js'),
			output: {
				assetFileNames: 'css/[name]-[hash][extname]',
				chunkFileNames: 'js/[name]-[hash].js',
				entryFileNames: 'js/[name]-[hash].js',
			},
		},
	},
	css: {
		devSourcemap: true,
	},
	server: {
		host: '0.0.0.0',
		port: 5173,
		strictPort: true,
		origin: 'http://silveira.localhost',
		cors: true,
	},
});
