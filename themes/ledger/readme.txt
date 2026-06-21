=== Ledger ===
Contributors: wpaithemes
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: news, magazine, blog, custom-menu, featured-images, two-column, translation-ready, editorial

An authoritative editorial and magazine theme with a real masthead, a full-width lead-story homepage, and a CSS drop cap on single posts.

== Description ==

Ledger is a print-inspired WordPress theme for news, magazines, and editorial
blogs. It pairs an off-white paper background with near-black serif type and a
single red accent for an authoritative newsstand feel.

The front page opens like a real newspaper: a dateline strip, a centered masthead
flanked by hairline rules, a sticky navigation bar, and a full-width LEAD STORY
that places a large featured image beside a bold headline, small-caps byline, and
standfirst. Below the fold, a "More Stories" grid lays out secondary articles in a
two-column hierarchy with category kickers and excerpts, while a column-ruled
sidebar carries the site's widgets.

Single posts are tuned for long-form reading: a comfortable measure, a generous
type scale, a CSS drop cap on the first paragraph, styled blockquotes and
pullquotes, and threaded comments. Featured images are rendered tastefully on the
index, single posts, and pages, and every layout degrades gracefully when a post
has no image.

Features:

* Full-width lead-story homepage with a masthead, dateline, and sticky nav
* Signature "LATEST" headline ticker and a crisp, accessible motion system
  (scroll reveals, drawing rule-lines, reading-progress bar, duotone hovers)
* "More Stories" two-column grid for secondary articles
* Column-ruled article + sidebar layout
* Serif body type tuned for long-form reading (comfortable measure)
* CSS drop cap on the first paragraph of single posts
* Small-caps bylines and category kickers above headlines
* Tasteful featured-image treatment on index, single, and page templates
* Custom logo, primary navigation menu (with dropdowns), and a widgetized sidebar
* Threaded comments, accessible focus states, and prefers-reduced-motion support
* Block editor color palette and font sizes via theme.json
* Translation-ready (text domain: ledger)

== Installation ==

1. In wp-admin go to Appearance > Themes > Add New > Upload Theme.
2. Choose ledger.zip and click Install Now.
3. Click Activate.
4. Under Settings > Reading, set your homepage to display your latest posts to get
   the full lead-story treatment.

== Changelog ==

= 1.3.0 =
* New signature "LATEST" headline ticker beneath the masthead — a seamless, pausable newswire marquee of recent posts.
* Added a crisp, journalistic motion system: staggered scroll reveals for the lead story, "More Stories" cards, and archive entries via IntersectionObserver.
* Masthead hairlines and section-heading rules now draw across on entry; the dateline settles in with a press-start cue on load.
* Thin red reading-progress bar fills as you read a single post.
* Duotone-to-colour contrast shift on featured-image hover.
* All motion is self-contained vanilla JS/CSS (no libraries), animates only transform/opacity, and is fully gated behind prefers-reduced-motion. Progressive enhancement: all content stays visible without JS via an `html.js` flag.

= 1.2.0 =
* Added Customizer "Colors & Style" controls (accent, background, and surface colors) with instant live preview.
* Site title and tagline now update live in the Customizer via selective refresh.
* Derived accent shades follow the chosen accent automatically via color-mix(), so one control restyles the whole theme.

= 1.1.0 =
* New magazine front page: dateline strip, centered masthead, and a full-width lead story.
* Added a "More Stories" secondary grid with featured-image cards.
* Refined typography, color contrast, sidebar widgets, comments, and navigation (with dropdowns and sticky bar).
* Tasteful featured-image heroes on single posts and pages; graceful no-image fallback.
* Accessibility: stronger focus states, reduced-motion and print styles, improved escaping.
* Updated screenshot, theme.json palette, and editor styles.

= 1.0.0 =
* Initial release.
