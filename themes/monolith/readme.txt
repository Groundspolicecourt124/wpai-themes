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
* Hand-rolled motion system: a brutalist marquee ticker, clip-path scroll
  reveals, magnetic hovers, a custom blocky cursor, count-up index numbers, and
  a scroll-progress rule — all vanilla JS, no external libraries or fonts
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

= 1.3.0 =
* New hand-rolled motion system (assets/js/motion.js) — vanilla JS, no
  libraries or CDNs, deferred and footer-loaded. Adds: clip-path / wipe scroll
  reveals with a staggered cascade (IntersectionObserver, one-shot), magnetic
  oversized link/button hovers, an animated index/entry counter that ticks up,
  a thin electric scroll-progress rule, and a pulsing accent square.
* Signature move: a horizontal brutalist MARQUEE ticker ("SELECTED WORK ///")
  under the homepage masthead, plus a custom blocky accent cursor that trails
  the pointer and snaps onto interactive targets, and a hard diagonal accent
  shard that wipes across project covers on hover.
* Fully accessible: respects prefers-reduced-motion (JS reveals everything
  immediately and skips all interactions; a CSS reduced-motion block disables
  every animation/transition, the custom cursor, and the progress bar).
  Keyboard focus is never trapped or hidden.
* Progressive enhancement: a tiny inline head snippet adds a `js` class to
  <html>, so with JavaScript disabled all content is fully visible and the
  decorative bar/cursor never appear. Only transform/opacity animate — no
  layout thrash and no cumulative layout shift.

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
