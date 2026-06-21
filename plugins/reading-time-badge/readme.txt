=== Reading Time Badge ===
Contributors: wpaithemes
Tags: reading time, posts, badge, content, accessibility
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.3.0
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
  technology, the label is fully translatable, and `prefers-reduced-motion` is honored
  at every layer — reduced-motion visitors get the badge with no animation at all.
* **Crafted entrance.** When the badge scrolls into view it fades and slides up a few
  pixels, and the clock hands take one gentle sweep. The motion animates only
  `transform` and `opacity` (no layout shift) and is pure progressive enhancement —
  with JavaScript off, or if it fails, the badge is simply visible from the start.
* **Zero layout shift.** The badge ships as real, self-contained CSS and JS asset files
  with fixed sizing — no images and no web fonts — so it never shifts the page.
* **Self-contained.** No external libraries, CDNs, network calls, or tracking. The
  scroll reveal is hand-rolled with vanilla `IntersectionObserver`.

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

= 1.3.0 =
* Added a crafted, accessible entrance: the badge fades and slides up as it scrolls
  into view, and the clock hands take one gentle sweep on reveal.
* Motion is hand-rolled with vanilla JavaScript (`IntersectionObserver`) — no external
  libraries or CDNs — and ships as a real enqueued asset (`assets/js/motion.js`) loaded
  in the footer with `defer`.
* Animates only `transform` and `opacity`, so the entrance adds zero layout shift.
* Fully respects `prefers-reduced-motion: reduce` (JS gate plus a CSS safety net) and
  degrades gracefully: with JavaScript off, or if it errors, the badge is visible from
  the start (progressive enhancement via an early `rtb-js` class on `<html>`).

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
