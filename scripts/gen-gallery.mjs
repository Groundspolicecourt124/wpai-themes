// Generate docs/gallery.json (consumed by the gallery UI) plus one WordPress
// Playground blueprint per item (docs/playground/<slug>.json) that installs and
// activates the theme/plugin straight from its zip on the deployed site.
import { writeFileSync } from 'node:fs';
import { join } from 'node:path';
import {
  listThemes, listPlugins, DOCS_DIR, SITE_URL, ensureDir,
  parseThemeHeader, parsePluginHeader,
} from './lib.mjs';
import { DEMO_PACKS, DEFAULT_PACK } from './demo-packs.mjs';

function tagList(v) {
  return v ? v.split(',').map((s) => s.trim()).filter(Boolean) : [];
}

const playgroundDir = join(DOCS_DIR, 'playground');
ensureDir(playgroundDir);

// A real, per-theme demo site so each live preview feels inhabited: posts with
// featured images, categories, an About page, a nav menu, comments, and an author.
// Per-theme content comes from demo-packs.mjs; missing themes use DEFAULT_PACK.
const IMG = (name) => `${SITE_URL}demo/${name}.jpg`;

// Resolve a pack's cover base names (e.g. "dusk") to absolute image URLs.
function resolvePack(pack) {
  const img = (name) => (name ? IMG(name) : '');
  return {
    site: pack.site,
    author: pack.author,
    menuCategories: pack.menuCategories || [],
    pages: (pack.pages || []).map((p) => ({ ...p, image: img(p.image) })),
    posts: (pack.posts || []).map((p) => ({ ...p, image: img(p.image) })),
    comments: pack.comments || [],
  };
}


function buildDemoPhp(pack) {
  const d = resolvePack(pack);
  return (
  "<?php\n" +
  "require_once '/wordpress/wp-load.php';\n" +
  "require_once '/wordpress/wp-admin/includes/media.php';\n" +
  "require_once '/wordpress/wp-admin/includes/file.php';\n" +
  "require_once '/wordpress/wp-admin/includes/image.php';\n" +
  "$d = json_decode(<<<'WPAIJSON'\n" +
  JSON.stringify(d) + "\n" +
  "WPAIJSON, true);\n" +
  "if ( ! is_array( $d ) ) { return; }\n" +
  "function wpai_img( $url, $post_id = 0 ) {\n" +
  "  if ( ! $url ) { return 0; }\n" +
  "  $tmp = download_url( $url );\n" +
  "  if ( is_wp_error( $tmp ) ) { return 0; }\n" +
  "  $file = array( 'name' => basename( parse_url( $url, PHP_URL_PATH ) ), 'tmp_name' => $tmp );\n" +
  "  $id = media_handle_sideload( $file, $post_id );\n" +
  "  if ( is_wp_error( $id ) ) { @unlink( $tmp ); return 0; }\n" +
  "  return (int) $id;\n" +
  "}\n" +
  "function wpai_cat( $name ) {\n" +
  "  $t = term_exists( $name, 'category' );\n" +
  "  if ( ! $t ) { $t = wp_insert_term( $name, 'category' ); }\n" +
  "  if ( is_wp_error( $t ) ) { return 0; }\n" +
  "  return (int) ( is_array( $t ) ? $t['term_id'] : $t );\n" +
  "}\n" +
  "update_option( 'blogname', $d['site']['name'] );\n" +
  "update_option( 'blogdescription', $d['site']['tagline'] );\n" +
  "wp_update_user( array( 'ID' => 1, 'display_name' => $d['author']['name'], 'nickname' => $d['author']['name'], 'description' => $d['author']['bio'] ) );\n" +
  "foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ) ) as $def ) {\n" +
  "  $ex = get_page_by_path( $def[0], OBJECT, $def[1] );\n" +
  "  if ( $ex ) { wp_delete_post( $ex->ID, true ); }\n" +
  "}\n" +
  "$page_ids = array();\n" +
  "foreach ( $d['pages'] as $pg ) {\n" +
  "  $id = wp_insert_post( array( 'post_type' => 'page', 'post_title' => $pg['title'], 'post_content' => $pg['content'], 'post_status' => 'publish', 'post_author' => 1 ) );\n" +
  "  if ( $id && ! empty( $pg['image'] ) ) { $a = wpai_img( $pg['image'], $id ); if ( $a ) { set_post_thumbnail( $id, $a ); } }\n" +
  "  if ( $id ) { $page_ids[ $pg['title'] ] = $id; }\n" +
  "}\n" +
  "$cat_ids = array();\n" +
  "$first = 0; $i = 0;\n" +
  "foreach ( $d['posts'] as $post ) {\n" +
  "  $i++;\n" +
  "  $pid = wp_insert_post( array( 'post_title' => $post['title'], 'post_content' => $post['content'], 'post_excerpt' => isset( $post['excerpt'] ) ? $post['excerpt'] : '', 'post_status' => 'publish', 'post_author' => 1, 'post_date' => gmdate( 'Y-m-d H:i:s', time() - $i * 86400 ) ) );\n" +
  "  if ( ! $pid ) { continue; }\n" +
  "  if ( ! $first ) { $first = $pid; }\n" +
  "  if ( ! empty( $post['category'] ) ) { $c = wpai_cat( $post['category'] ); if ( $c ) { wp_set_post_categories( $pid, array( $c ) ); $cat_ids[ $post['category'] ] = $c; } }\n" +
  "  if ( ! empty( $post['image'] ) ) { $a = wpai_img( $post['image'], $pid ); if ( $a ) { set_post_thumbnail( $pid, $a ); } }\n" +
  "}\n" +
  "if ( $first && ! empty( $d['comments'] ) ) {\n" +
  "  $h = 1;\n" +
  "  foreach ( $d['comments'] as $cm ) {\n" +
  "    wp_insert_comment( array( 'comment_post_ID' => $first, 'comment_author' => $cm['author'], 'comment_content' => $cm['content'], 'comment_approved' => 1, 'comment_date' => gmdate( 'Y-m-d H:i:s', time() - $h * 3600 ) ) );\n" +
  "    $h++;\n" +
  "  }\n" +
  "}\n" +
  "$menu_id = wp_create_nav_menu( 'Demo Menu' );\n" +
  "if ( ! is_wp_error( $menu_id ) ) {\n" +
  "  wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'Home', 'menu-item-url' => home_url( '/' ), 'menu-item-status' => 'publish' ) );\n" +
  "  if ( ! empty( $page_ids['About'] ) ) { wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => 'About', 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids['About'], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) ); }\n" +
  "  foreach ( $d['menuCategories'] as $cn ) { if ( ! empty( $cat_ids[ $cn ] ) ) { wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $cn, 'menu-item-object' => 'category', 'menu-item-object-id' => $cat_ids[ $cn ], 'menu-item-type' => 'taxonomy', 'menu-item-status' => 'publish' ) ); } }\n" +
  "  $regs = get_registered_nav_menus();\n" +
  "  $locs = get_theme_mod( 'nav_menu_locations' );\n" +
  "  if ( ! is_array( $locs ) ) { $locs = array(); }\n" +
  "  if ( is_array( $regs ) && $regs ) { foreach ( array_keys( $regs ) as $loc ) { $locs[ $loc ] = $menu_id; } } else { $locs['primary'] = $menu_id; }\n" +
  "  set_theme_mod( 'nav_menu_locations', $locs );\n" +
  "}\n"
  );
}

function demoSteps(slug) {
  const pack = DEMO_PACKS[slug] || DEFAULT_PACK;
  return [{ step: 'runPHP', code: buildDemoPhp(pack) }];
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
    steps: [install, ...demoSteps(slug)],
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
