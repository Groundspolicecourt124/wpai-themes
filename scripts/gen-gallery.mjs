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

// Demo content so live previews look like a real site, not a bare "Hello world".
const DEMO_POSTS = [
  {
    title: 'Designing for calm',
    content:
      '<p>Good software feels quiet. It does the thing you asked, then steps out of the way and leaves room for your own thoughts.</p>' +
      '<h2>Less, but better</h2>' +
      '<p>Every element on a page is a small request for attention. The craft is in deciding which requests are worth making.</p>' +
      '<blockquote>Simplicity is not the absence of detail. It is detail spent only where it counts.</blockquote>' +
      '<p>When in doubt, remove. The page that remains is almost always stronger.</p>',
  },
  {
    title: 'The cost of a fast website',
    content:
      '<p>Speed is a feature you feel before you can name it. A page that loads instantly feels trustworthy; a slow one feels broken even when it works.</p>' +
      '<h2>Where the milliseconds go</h2>' +
      '<ul><li>Fonts that block the first paint</li><li>Images larger than their containers</li><li>Scripts that run before anyone has scrolled</li></ul>' +
      '<p>Trim each one and the whole experience lightens.</p>',
  },
  {
    title: 'Notes on shipping small',
    content:
      '<p>The smallest version of an idea that still helps someone is usually the right place to start. You learn more from one real user than from a month of speculation.</p>' +
      '<h2>Ship, then listen</h2>' +
      '<p>Release early, watch closely, and let the next step reveal itself. Momentum compounds.</p>',
  },
];

const DEMO_PHP =
  "<?php\n" +
  "require_once '/wordpress/wp-load.php';\n" +
  "$json = <<<'WPAIJSON'\n" +
  JSON.stringify(DEMO_POSTS) + "\n" +
  "WPAIJSON;\n" +
  "$posts = json_decode( $json, true );\n" +
  "if ( is_array( $posts ) ) {\n" +
  "  foreach ( $posts as $p ) {\n" +
  "    wp_insert_post( array(\n" +
  "      'post_title'   => $p['title'],\n" +
  "      'post_content' => $p['content'],\n" +
  "      'post_status'  => 'publish',\n" +
  "      'post_author'  => 1,\n" +
  "    ) );\n" +
  "  }\n" +
  "}\n";

function demoSteps() {
  return [
    { step: 'setSiteOptions', options: { blogname: 'Northwind', blogdescription: 'Ideas worth shipping.' } },
    { step: 'runPHP', code: DEMO_PHP },
  ];
}

function writeBlueprint(slug, kind) {
  const zipUrl = `${SITE_URL}downloads/${slug}.zip`;
  const install = kind === 'theme'
    ? { step: 'installTheme', themeZipFile: { resource: 'url', url: zipUrl }, options: { activate: true } }
    : { step: 'installPlugin', pluginZipFile: { resource: 'url', url: zipUrl }, options: { activate: true } };
  const blueprint = {
    $schema: 'https://playground.wordpress.net/blueprint-schema.json',
    landingPage: '/',
    preferredVersions: { php: '8.0', wp: 'latest' },
    features: { networking: true },
    steps: [install, ...demoSteps()],
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

// robots.txt + sitemap.xml for search engines.
writeFileSync(
  join(DOCS_DIR, 'robots.txt'),
  `User-agent: *\nAllow: /\n\nSitemap: ${SITE_URL}sitemap.xml\n`
);
writeFileSync(
  join(DOCS_DIR, 'sitemap.xml'),
  '<?xml version="1.0" encoding="UTF-8"?>\n' +
  '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n' +
  `  <url>\n    <loc>${SITE_URL}</loc>\n    <lastmod>${data.generatedAt.slice(0, 10)}</lastmod>\n` +
  '    <changefreq>weekly</changefreq>\n    <priority>1.0</priority>\n  </url>\n' +
  '</urlset>\n'
);

console.log(`✓ gallery.json + sitemap.xml + robots.txt — ${themes.length} theme(s), ${plugins.length} plugin(s)`);
