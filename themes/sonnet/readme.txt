=== Sonnet ===
Contributors: wpaithemes
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: blog, one-column, custom-menu, featured-images, threaded-comments, translation-ready, dark, editor-style, full-width-template

A dark-mode-first longform theme for writers, built around comfortable reading.

== Description ==

Sonnet is a quiet, literary WordPress theme for writers who care about the words.
It opens in dark mode by default — deep charcoal behind warm off-white serif type
at a narrow, comfortable measure — with a muted gold accent, italic pull-quotes,
and restrained hairline ornament.

The homepage reads like a journal rather than a feed: a centered masthead crowned
by your tagline, a featured lead essay shown large with its cover image and a
drop-cap excerpt, then a refined "more reading" list with compact cover
thumbnails. Single posts get a centered title, a full-bleed cover, a drop-cap
lead-in, and threaded comments. No page builders, no bloat, no tracking — just a
calm, focused place to publish long-form work.

Features:

* Dark-first, single-column reading layout at a comfortable ~66ch serif measure
* Literary homepage: masthead + featured lead essay + refined reading list
* Drop-cap lead-ins, italic pull-quotes, and ornamental hairline dividers
* Tasteful featured-image treatment on the index, single posts, and pages
* Category eyebrows and a "By Author · Date" byline on every post
* Deliberate 1.25 type scale with old-style numerals and web-safe font stacks
* Custom logo, primary navigation menu, and a widgetized footer rail
* Threaded comments, accessible :focus-visible states, and a skip link
* Respects prefers-reduced-motion; WCAG AA contrast throughout
* Block editor color palette, font sizes, and editor styles via theme.json
* Translation-ready (text domain: sonnet)

== Installation ==

1. In wp-admin go to Appearance > Themes > Add New > Upload Theme.
2. Choose sonnet.zip and click Install Now.
3. Click Activate.
4. (Optional) Set a Tagline under Settings > General — it anchors the homepage
   masthead. Add a Primary menu under Appearance > Menus and widgets under
   Appearance > Widgets.

== Changelog ==

= 1.2.0 =
* Added a "Colors & Style" section to Appearance > Customize with live preview:
  Accent, Background, and Surface color controls. Changing the Accent color
  cascades to every derived gold shade (highlight, deep, soft, glow) via
  color-mix(), so the whole theme recolors instantly with no code. The site
  title and tagline also update live via selective refresh.

= 1.1.0 =
* New literary homepage: centered masthead, featured lead essay with cover image
  and drop-cap excerpt, and a refined "more reading" list with thumbnails.
* Reworked single/page reading view: centered title, category eyebrow, full-bleed
  cover image with optional caption, drop-cap lead-in, and tag/category footer.
* Polished components throughout: nav with animated underlines and sub-menus,
  card-style post-to-post navigation, richer comments, tag-pill footers, and a
  more helpful 404 listing recent essays.
* Expanded, accessible CSS: AA contrast, focus rings, reduced-motion support, and
  a mobile-first responsive grid from 360px to 1280px.
* theme.json palette, font sizes, and layout aligned with the stylesheet; added
  editor styles, a card image size, and refined excerpts.

= 1.0.0 =
* Initial release.
