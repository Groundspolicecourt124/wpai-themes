=== Reading Time Badge ===
Contributors: wpaithemes
Tags: reading time, posts, badge, content, accessibility
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a tasteful "X min read" badge with a small clock glyph above the content of single posts. Theme-adaptive, accessible, zero configuration.

== Description ==

Reading Time Badge estimates how long a post takes to read (based on ~220 words per
minute) and shows a small, unobtrusive badge above the post content. It works on any
theme, adds no settings page, makes no external requests, and stores no data.

* **Theme-adaptive.** Colors are derived from the surrounding text via `currentColor`,
  so the badge looks right on both light and dark themes. Themes can override the
  `--rtb-*` custom properties to restyle it.
* **Accessible.** A decorative inline clock glyph (SVG) is hidden from assistive
  technology, the label is fully translatable, and `prefers-reduced-motion` is honored.
* **Zero layout shift.** The badge ships as a real, self-contained CSS file with fixed
  sizing — no images, web fonts, or JavaScript — so it never shifts the page.
* **Self-contained.** No external network calls, no third-party services, no tracking.

Developers can tune the reading speed:

`add_filter( 'rtb_words_per_minute', fn() => 250 );`

Or adjust the final computed minutes directly:

`add_filter( 'rtb_estimate_minutes', fn( $minutes ) => $minutes + 1 );`

== Installation ==

1. In wp-admin go to Plugins > Add New > Upload Plugin.
2. Choose reading-time-badge.zip and click Install Now.
3. Click Activate. That's it — open any post to see the badge.

== Frequently Asked Questions ==

= Does it work on dark themes? =

Yes. The badge tints itself from the current text color rather than hardcoded grays,
so it adapts to light and dark themes automatically.

= Can I change the words-per-minute speed? =

Yes — use the `rtb_words_per_minute` filter. To override the final minute count
directly, use the `rtb_estimate_minutes` filter.

= Does it add a settings page or make network requests? =

No. It is zero-config and fully self-contained — no settings page, no external
requests, and no stored data.

== Changelog ==

= 1.1.0 =
* Added an inline clock glyph (decorative SVG, hidden from assistive technology).
* Redesigned the badge to be theme-adaptive — derives its colors from `currentColor`
  so it reads correctly on both light and dark themes.
* Moved styles into a real, enqueued CSS asset file (no inline blobs).
* Reserved fixed sizing to guarantee zero cumulative layout shift.
* Reading time now ignores shortcodes for a more accurate word count.
* Added the `rtb_estimate_minutes` filter and translation (text domain) loading.
* Honors `prefers-reduced-motion` and adds an accessible focus style.

= 1.0.0 =
* Initial release.
