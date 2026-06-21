// Shared helpers for the build scripts. Themes/plugins are the source of truth;
// everything here just reads their standard WordPress headers.
import { readFileSync, readdirSync, existsSync, mkdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join, resolve } from 'node:path';

const __dirname = dirname(fileURLToPath(import.meta.url));
export const ROOT = resolve(__dirname, '..');
export const THEMES_DIR = join(ROOT, 'themes');
export const PLUGINS_DIR = join(ROOT, 'plugins');
export const DOCS_DIR = join(ROOT, 'docs');

// Absolute base URL of the deployed gallery (trailing slash guaranteed).
// Overridable via SITE_URL env so CI can bake the real Pages URL into blueprints.
export const SITE_URL = (process.env.SITE_URL ||
  'https://lordbasilaiassistant-sudo.github.io/wpai-themes/').replace(/\/?$/, '/');

export function ensureDir(p) {
  mkdirSync(p, { recursive: true });
}

function subdirs(dir) {
  if (!existsSync(dir)) return [];
  return readdirSync(dir, { withFileTypes: true })
    .filter((d) => d.isDirectory())
    .map((d) => ({ slug: d.name, dir: join(dir, d.name) }));
}

export function listThemes() {
  return subdirs(THEMES_DIR).filter((t) => existsSync(join(t.dir, 'style.css')));
}

export function listPlugins() {
  const out = [];
  for (const p of subdirs(PLUGINS_DIR)) {
    const phps = readdirSync(p.dir).filter((f) => f.endsWith('.php'));
    const preferred = `${p.slug}.php`;
    const ordered = phps.includes(preferred)
      ? [preferred, ...phps.filter((f) => f !== preferred)]
      : phps;
    const main = ordered.find((f) =>
      /Plugin Name\s*:/i.test(readFileSync(join(p.dir, f), 'utf8'))
    );
    if (main) out.push({ ...p, mainFile: main });
  }
  return out;
}

// Parse a WordPress-style "Key: Value" header from the first /* ... */ comment block.
function parseHeaderBlock(text) {
  const m = text.match(/\/\*([\s\S]*?)\*\//);
  const block = m ? m[1] : text;
  const fields = {};
  for (const line of block.split(/\r?\n/)) {
    const mm = line.match(/^\s*\*?\s*([A-Za-z][A-Za-z \-]+?)\s*:\s*(.+?)\s*$/);
    if (mm) fields[mm[1].trim()] = mm[2].trim();
  }
  return fields;
}

export function parseThemeHeader(t) {
  return parseHeaderBlock(readFileSync(join(t.dir, 'style.css'), 'utf8'));
}

export function parsePluginHeader(p) {
  return parseHeaderBlock(readFileSync(join(p.dir, p.mainFile), 'utf8'));
}
