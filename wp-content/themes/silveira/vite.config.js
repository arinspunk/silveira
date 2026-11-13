import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig(({ command }) => ({
	// Build: relative paths, so CSS url()s (fonts) point to assets/fonts/ from assets/css/.
	// Dev: the theme path, used by functions.php to load @vite/client and the entry.
	base: command === 'build' ? './' : '/wp-content/themes/silveira/',
	build: {
		outDir: 'assets',
		emptyOutDir: true,
		manifest: true,
		rollupOptions: {
			input: path.resolve(__dirname, 'src/main.js'),
			output: {
				assetFileNames: (asset) =>
					/\.(woff2?|ttf|otf)$/.test(asset.names?.[0] ?? asset.name ?? '')
						? 'fonts/[name]-[hash][extname]'
						: 'css/[name]-[hash][extname]',
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
}));
