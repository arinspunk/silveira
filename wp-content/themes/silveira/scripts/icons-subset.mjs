// Regenera src/fonts/material-symbols-subset.woff2: subconjunto de Material Symbols Outlined
// con solo los iconos de src/material-symbols-icons.txt (uno por línea).
// Uso (en el theme): npm run icons:subset
// Se ejecuta a mano al añadir un icono; el sitio nunca pide nada a Google Fonts.
import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const listFile = path.join(root, 'src/material-symbols-icons.txt');
const outFile = path.join(root, 'src/fonts/material-symbols-subset.woff2');

// Mismos ejes que usa _icons.scss (FILL, wght 200–700, opsz, GRAD).
const AXES = 'opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200';
// Con un User-Agent de navegador moderno Google Fonts sirve woff2.
const UA =
	'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36';

const names = [
	...new Set(
		(await readFile(listFile, 'utf8'))
			.split('\n')
			.map((l) => l.trim())
			.filter((l) => l && !l.startsWith('#')),
	),
].sort(); // icon_names exige orden alfabético.

for (const n of names) {
	if (!/^[a-z0-9_]+$/.test(n)) {
		throw new Error(`Nombre de icono no válido en ${listFile}: "${n}"`);
	}
}

const cssUrl = `https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:${AXES}&icon_names=${names.join(',')}&display=block`;
const css = await (await fetch(cssUrl, { headers: { 'User-Agent': UA } })).text();
const fontUrl = css.match(/url\((https:[^)]+)\)\s*format\('woff2'\)/)?.[1];
if (!fontUrl) {
	throw new Error(`Google Fonts no devolvió un woff2:\n${css.slice(0, 500)}`);
}

const res = await fetch(fontUrl, { headers: { 'User-Agent': UA } });
if (!res.ok) {
	throw new Error(`Descarga fallida (${res.status}): ${fontUrl}`);
}
const buf = Buffer.from(await res.arrayBuffer());
await writeFile(outFile, buf);
console.log(
	`${names.length} iconos → ${path.relative(root, outFile)} (${(buf.length / 1024).toFixed(1)} KB)`,
);
