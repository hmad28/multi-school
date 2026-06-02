// @ts-check
import { defineConfig } from 'astro/config';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

import tailwindcss from '@tailwindcss/vite';
import sitemap from '@astrojs/sitemap';

const root = path.dirname(fileURLToPath(import.meta.url));

// https://astro.build/config
export default defineConfig({
	site: 'https://platformsekolah.id',
	vite: {
		plugins: [tailwindcss()],
		resolve: {
			// Hindari Tailwind v3 dari root Laravel (multi-school/node_modules)
			alias: {
				tailwindcss: path.resolve(root, 'node_modules/tailwindcss'),
			},
		},
	},
	integrations: [sitemap()],
});
