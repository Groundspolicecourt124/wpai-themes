// Rasterize each theme/plugin's screenshot.svg → screenshot.png (1200x900).
// Writes a copy inside the theme/plugin folder (WordPress needs screenshot.png in
// the zip) and one into docs/screenshots/ for the gallery. Falls back to a
// branded placeholder when no screenshot.svg is provided.
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { join } from 'node:path';
import sharp from 'sharp';
import {
  listThemes, listPlugins, DOCS_DIR, ensureDir,
  parseThemeHeader, parsePluginHeader,
} from './lib.mjs';

const W = 1200, H = 900;

function esc(s) {
  return String(s).replace(/[<&>]/g, (c) => ({ '<': '&lt;', '&': '&amp;', '>': '&gt;' }[c]));
}

function fallbackSvg(name, subtitle, accent = '#6366f1') {
  return `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
  <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
    <stop offset="0" stop-color="${accent}"/><stop offset="1" stop-color="#0f172a"/></linearGradient></defs>
  <rect width="${W}" height="${H}" fill="url(#g)"/>
  <text x="80" y="440" font-family="Georgia, 'Times New Roman', serif" font-size="92" fill="#ffffff" font-weight="700">${esc(name)}</text>
  <text x="84" y="510" font-family="Arial, sans-serif" font-size="32" fill="#e2e8f0">${esc(subtitle)}</text>
</svg>`;
}

async function rasterize(svgBuf, pngPath) {
  await sharp(svgBuf, { density: 220 }).resize(W, H, { fit: 'cover' }).png().toFile(pngPath);
}

// Rasterize, but never let one malformed SVG break the whole build — fall back
// to the branded placeholder if sharp can't render it.
async function rasterizeSafe(svgBuf, pngPath, name, sub) {
  try {
    await rasterize(svgBuf, pngPath);
  } catch (err) {
    console.warn(`  !! ${name}: screenshot.svg failed (${err.message}); using placeholder`);
    await rasterize(Buffer.from(fallbackSvg(name, sub)), pngPath);
  }
}

const screenshotsDir = join(DOCS_DIR, 'screenshots');
ensureDir(screenshotsDir);

const items = [
  ...listThemes().map((t) => ({ ...t, kind: 'theme', header: parseThemeHeader(t) })),
  ...listPlugins().map((p) => ({ ...p, kind: 'plugin', header: parsePluginHeader(p) })),
];

if (items.length === 0) console.log('  (no themes or plugins yet)');

for (const it of items) {
  const svgPath = join(it.dir, 'screenshot.svg');
  let svgBuf;
  if (existsSync(svgPath)) {
    svgBuf = readFileSync(svgPath);
  } else {
    const name = it.header['Theme Name'] || it.header['Plugin Name'] || it.slug;
    const sub = (it.header['Description'] || '').slice(0, 64);
    svgBuf = Buffer.from(fallbackSvg(name, sub));
  }
  const name = it.header['Theme Name'] || it.header['Plugin Name'] || it.slug;
  const sub = (it.header['Description'] || '').slice(0, 64);
  await rasterizeSafe(svgBuf, join(it.dir, 'screenshot.png'), name, sub);
  await rasterizeSafe(svgBuf, join(screenshotsDir, `${it.slug}.png`), name, sub);
  console.log(`  shot  ${it.kind.padEnd(6)} ${it.slug}.png`);
}
console.log(`✓ ${items.length} screenshot(s)`);
