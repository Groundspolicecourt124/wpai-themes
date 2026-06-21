// Generate docs/gallery.json (consumed by the gallery UI) plus one WordPress
// Playground blueprint per item (docs/playground/<slug>.json) that installs and
// activates the theme/plugin straight from its zip on the deployed site.
import { writeFileSync } from 'node:fs';
import { join } from 'node:path';
import {
  listThemes, listPlugins, DOCS_DIR, SITE_URL, ensureDir,
  parseThemeHeader, parsePluginHeader,
} from './lib.mjs';

function tagList(v) {
  return v ? v.split(',').map((s) => s.trim()).filter(Boolean) : [];
}

const playgroundDir = join(DOCS_DIR, 'playground');
ensureDir(playgroundDir);

function writeBlueprint(slug, kind) {
  const zipUrl = `${SITE_URL}downloads/${slug}.zip`;
  const step = kind === 'theme'
    ? { step: 'installTheme', themeZipFile: { resource: 'url', url: zipUrl }, options: { activate: true } }
    : { step: 'installPlugin', pluginZipFile: { resource: 'url', url: zipUrl }, options: { activate: true } };
  const blueprint = {
    $schema: 'https://playground.wordpress.net/blueprint-schema.json',
    landingPage: '/',
    preferredVersions: { php: '8.0', wp: 'latest' },
    features: { networking: true },
    steps: [step],
  };
  writeFileSync(join(playgroundDir, `${slug}.json`), JSON.stringify(blueprint, null, 2));
}

function themeEntry(t) {
  const h = parseThemeHeader(t);
  writeBlueprint(t.slug, 'theme');
  return {
    slug: t.slug, type: 'theme',
    name: h['Theme Name'] || t.slug,
    description: h['Description'] || '',
    version: h['Version'] || '1.0.0',
    author: h['Author'] || 'WPAI Themes',
    tags: tagList(h['Tags']),
    requiresWp: h['Requires at least'] || '',
    requiresPhp: h['Requires PHP'] || '',
    license: h['License'] || 'GPL-2.0-or-later',
    screenshot: `screenshots/${t.slug}.png`,
    zip: `downloads/${t.slug}.zip`,
    blueprint: `playground/${t.slug}.json`,
  };
}

function pluginEntry(p) {
  const h = parsePluginHeader(p);
  writeBlueprint(p.slug, 'plugin');
  return {
    slug: p.slug, type: 'plugin',
    name: h['Plugin Name'] || p.slug,
    description: h['Description'] || '',
    version: h['Version'] || '1.0.0',
    author: h['Author'] || 'WPAI Themes',
    tags: [],
    requiresPhp: h['Requires PHP'] || '',
    license: h['License'] || 'GPL-2.0-or-later',
    screenshot: `screenshots/${p.slug}.png`,
    zip: `downloads/${p.slug}.zip`,
    blueprint: `playground/${p.slug}.json`,
  };
}

const themes = listThemes().map(themeEntry);
const plugins = listPlugins().map(pluginEntry);

const data = {
  generatedAt: new Date().toISOString(),
  site: SITE_URL,
  counts: { themes: themes.length, plugins: plugins.length },
  themes,
  plugins,
};

writeFileSync(join(DOCS_DIR, 'gallery.json'), JSON.stringify(data, null, 2));
console.log(`✓ gallery.json — ${themes.length} theme(s), ${plugins.length} plugin(s)`);
