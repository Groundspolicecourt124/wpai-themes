=== Monolith ===
Contributors: wpaithemes
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: portfolio, two-column, custom-menu, featured-images, threaded-comments, translation-ready, editor-style, full-width-template, blog, dark

A bold brutalist portfolio and agency theme: oversized type, hard edges, and a stark grid.

== Description ==

Monolith is a dark, high-contrast WordPress theme for creative studios, agencies,
portfolios, and engineering blogs. It leans hard into brutalist design: an oversized
uppercase display masthead, a monospace UI, hard 1px borders with zero rounded corners,
an electric accent, and a project-grid post index that reads like a gallery wall.

The front page opens with a confident, oversized statement headline drawn from your site
tagline, followed by a two-up grid of project cards — each with a full-bleed featured
cover, a numbered index, a category tag, a byline, and an excerpt. Posts with no featured
image degrade gracefully to a brutalist placeholder.

Features:

* Oversized uppercase display masthead on the homepage built from the site tagline
* Brutalist dark palette (#0a0a0a ground, #f5f5f5 ink, #00e5a0 electric accent), all WCAG AA
* Project-grid post index with featured covers, numbered index, category tags, and bylines
* Web-safe / system font stacks only — no external or Google fonts loaded
* Monospace interface type for a confident, technical feel
* Sticky framed sidebar with styled Search, Recent Posts, Recent Comments, Archives, Categories
* Large framed featured images on single posts and pages
* Custom logo, primary navigation menu (with dropdowns), featured images, threaded comments
* Numbered pagination and styled comment form
* Visible :focus-visible states; respects prefers-reduced-motion
* Block editor color palette and font sizes via theme.json
* Translation-ready (text domain: monolith)

== Installation ==

1. In wp-admin go to Appearance > Themes > Add New > Upload Theme.
2. Choose monolith.zip and click Install Now.
3. Click Activate.
4. Set a Site Title and Tagline under Settings > General — the tagline becomes the
   oversized homepage statement headline.
5. Assign a menu to the "Primary Menu" location under Appearance > Menus.

== Changelog ==

= 1.2.0 =
* Added Customizer color controls (Appearance > Customize > "Colors & Style") with
  live preview: Accent, Background, and Surface colors update the whole theme
  instantly via CSS custom properties — no code required.
* Derived accent shades (hover, ink) now follow the chosen accent automatically
  via color-mix(), so a single color change cascades everywhere.
* Site title and tagline now update live in the Customizer (selective refresh).

= 1.1.0 =
* New oversized statement masthead on the front page (built from the site tagline).
* Reworked post index into a brutalist two-up project grid with featured covers,
  numbered index badges, category tags, and bylines; graceful no-image placeholder.
* Removed the external "Archivo Black" font reference; display type now uses a robust
  web-safe heavy stack (no external fonts loaded).
* Improved color contrast to meet WCAG AA across all text.
* Sticky header and sticky sidebar; refined hover/focus transitions with reduced-motion support.
* Numbered pagination, refined comment list/form, table and figure styling.
* Updated screenshot, theme.json palette/fonts, and bumped version.

= 1.0.0 =
* Initial release.
