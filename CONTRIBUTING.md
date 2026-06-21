# Contributing — Theme & Plugin Spec

This is the contract every theme and plugin in this repo follows. The build tooling and the
gallery depend on it, so follow it exactly. When in doubt, copy `themes/aurora/` (the
reference theme) or `plugins/reading-time-badge/` (the reference plugin).

---

## Themes

### Folder

One folder per theme at `themes/<slug>/`, where `<slug>` is lowercase, hyphenated, and unique
(e.g. `aurora`, `verdant-business`). The slug is the WordPress theme directory name and the
download filename.

### Required files

| File | Purpose |
|------|---------|
| `style.css` | **Header block** (below) + the theme's CSS. |
| `index.php` | Main fallback template (the loop). |
| `functions.php` | Theme setup, `wp_enqueue_*`, menus, widget areas, theme supports. |
| `header.php` | Opening HTML, `wp_head()`, site header/nav. |
| `footer.php` | Site footer, `wp_footer()`, closing HTML. |
| `sidebar.php` | Widget area (if the theme has one). |
| `single.php` | Single post template. |
| `page.php` | Single page template. |
| `comments.php` | Comments + comment form. |
| `searchform.php` | Search form markup. |
| `404.php` | Not-found template. |
| `template-parts/content.php` | Reusable post-content partial used by the loop. |
| `screenshot.svg` | 4:3 (1200×900) vector preview. Rasterized to `screenshot.png` at build. |
| `readme.txt` | WordPress-style readme (name, description, changelog). |

A minimal `theme.json` (editor color palette / font sizes) is encouraged but optional.

### `style.css` header (the source of truth for the gallery)

The header MUST be the first thing in the file, in this exact key format:

```css
/*
Theme Name: Aurora
Theme URI: https://lordbasilaiassistant-sudo.github.io/wpai-themes/
Author: WPAI Themes
Author URI: https://github.com/lordbasilaiassistant-sudo/wpai-themes
Description: A clean, airy personal-blog theme with serif headings and effortless reading.
Version: 1.0.0
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: aurora
Tags: blog, one-column, custom-menu, featured-images, threaded-comments, translation-ready, light
*/
```

`gen-gallery.mjs` parses `Theme Name`, `Description`, `Version`, `Author`, `Tags`,
`Requires at least`, `Requires PHP`, and `License` from this block. Get them right.

### Coding standards (non-negotiable)

- **Text domain = slug.** All user-facing strings wrapped: `esc_html__( 'Text', 'aurora' )`.
- **Escape on output**, always: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`.
- **No PHP closing tag** (`?>`) at the end of pure-PHP files.
- **Prefix** all functions/handles with the slug: `aurora_setup()`, `aurora-style`.
- **Enqueue** styles/scripts via `wp_enqueue_style/script` in `functions.php` — never hardcode
  `<link>`/`<script>` tags. Enqueue the main stylesheet with `get_stylesheet_uri()`.
- **Required `add_theme_support`:** `title-tag`, `post-thumbnails`, `automatic-feed-links`,
  `html5` (search-form, comment-form, comment-list, gallery, caption), `custom-logo`.
- **Register** at least one nav menu (`register_nav_menus`) and one sidebar
  (`register_sidebar`).
- `header.php` must call `wp_head()` and `body_class()`; `footer.php` must call `wp_footer()`.
- No external CDNs, no tracking, no phone-home, no bundled minified blobs of unknown origin.

### Design

Each theme should have a distinct, intentional point of view — not a generic Bootstrap look.
Ship real CSS (custom properties for colors/spacing, a type scale, responsive layout,
accessible focus states, dark-mode-friendly where it fits the concept). The `screenshot.svg`
should look like the actual rendered homepage.

---

## Plugins

One folder per plugin at `plugins/<slug>/`. The main file is `<slug>.php` and MUST open with
a plugin header:

```php
<?php
/**
 * Plugin Name: Reading Time Badge
 * Plugin URI:  https://lordbasilaiassistant-sudo.github.io/wpai-themes/
 * Description: Adds an estimated reading time above post content. Zero config.
 * Version:     1.0.0
 * Author:      WPAI Themes
 * License:     GPL-2.0-or-later
 * Text Domain: reading-time-badge
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // No direct access.
```

Rules:

- Guard against direct access (`if ( ! defined( 'ABSPATH' ) ) exit;`) at the top.
- Prefix everything with the slug (functions, hooks, option names, CSS handles).
- Escape on output, sanitize on input, use nonces for any form/admin action.
- Self-contained: no external network calls, no third-party services, no paid upsells.
- Include a `readme.txt` and an optional `screenshot.svg`.

---

## Build & validate

```bash
npm run build           # screenshots → zips → gallery.json (must succeed with no errors)
npm run dev <slug>      # boot the theme/plugin in local WordPress and click around
```

A contribution is ready when `npm run build` succeeds and the theme/plugin loads and renders
cleanly in `npm run dev`.
