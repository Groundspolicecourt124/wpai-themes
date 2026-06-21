// Boot real WordPress locally with a theme or plugin active — no Docker, no MySQL.
// Uses @wordpress/wp-now (PHP-WASM). Usage: npm run dev <slug>
import { spawn } from 'node:child_process';
import { existsSync } from 'node:fs';
import { join } from 'node:path';
import { listThemes, listPlugins, THEMES_DIR, PLUGINS_DIR } from './lib.mjs';

const name = process.argv[2];

if (!name) {
  console.log('Usage: npm run dev <theme-or-plugin-slug>\n');
  console.log('  Themes: ', listThemes().map((t) => t.slug).join(', ') || '(none yet)');
  console.log('  Plugins:', listPlugins().map((p) => p.slug).join(', ') || '(none yet)');
  process.exit(1);
}

let target = null;
if (existsSync(join(THEMES_DIR, name, 'style.css'))) target = join(THEMES_DIR, name);
else if (existsSync(join(PLUGINS_DIR, name))) target = join(PLUGINS_DIR, name);

if (!target) {
  console.error(`No theme or plugin named "${name}". Run "npm run dev" to list options.`);
  process.exit(1);
}

console.log(`▶ Booting WordPress with "${name}"  (first run downloads WP — give it a moment)`);
console.log('  When ready: http://localhost:8881   ·   wp-admin login: admin / password');
console.log('  Press Ctrl+C to stop.\n');

const npx = process.platform === 'win32' ? 'npx.cmd' : 'npx';
const child = spawn(npx, ['-y', '@wordpress/wp-now', 'start', '--path', target], {
  stdio: 'inherit',
  cwd: target,
});
child.on('exit', (code) => process.exit(code ?? 0));
