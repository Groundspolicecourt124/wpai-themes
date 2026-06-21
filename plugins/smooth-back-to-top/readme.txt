=== Smooth Back to Top ===
Contributors: wpaithemes
Tags: back to top, scroll, accessibility, button, navigation
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight, accessible floating button that appears on scroll and smoothly returns to the top.

== Description ==

Smooth Back to Top adds a single, delightful floating button to the bottom-right
corner of your site. An up-arrow sits inside a circular progress ring that fills as
the visitor reads down the page, so the control doubles as a live scroll indicator.
It fades in after the visitor scrolls down about 300 pixels and smoothly scrolls the
page back to the top when clicked.

It is built to be polite and accessible:

* Rendered as a native `<button>`, so it is fully keyboard-focusable and operable.
* Carries a descriptive `aria-label` and an up-arrow wrapped in a scroll-progress ring.
* A circular SVG ring tracks how far down the page you are (animating only `transform` and `opacity`-class properties — no layout shift, ever).
* Fades and scales in with a refined spring once you scroll past about 300 pixels, with a satisfying lift on hover.
* Respects the `prefers-reduced-motion` setting — it jumps instead of animating for users who ask for no motion (both in JS and via a CSS media query).
* Adapts to both light and dark themes via `prefers-color-scheme`.
* Keeps clear of mobile safe-area insets (such as the iOS home indicator).
* Progressive enhancement: the button works even if the script never loads.
* Moves focus to the top of the page after scrolling, so keyboard users keep their place.

It works on any theme, adds no settings page, makes no external requests, and stores no data.
The styles and the script are tiny, dependency-free files enqueued from the plugin's own
`assets` folder; the script loads in the footer so it never blocks rendering.

== Installation ==

1. In wp-admin go to Plugins > Add New > Upload Plugin.
2. Choose smooth-back-to-top.zip and click Install Now.
3. Click Activate. That's it — scroll down any page on the front end to see the button.

== Frequently Asked Questions ==

= Is there a settings page? =

No. The plugin is intentionally zero-configuration.

= Does it make any external requests or store data? =

No. Everything runs locally in the visitor's browser; nothing is sent anywhere and nothing
is saved to your database.

= Will it work with my theme? =

Yes. The button is fixed to the viewport and does not depend on any specific theme markup.

== Screenshots ==

1. The floating Back to Top button in the lower-right corner of a page.

== Changelog ==

= 1.3.0 =
* New: a circular scroll-progress ring now wraps the up-arrow and fills as you read down the page (driven by a single CSS custom property; animates stroke-dashoffset only).
* New: refined spring reveal and a satisfying hover lift/glow, with a brighter ring accent on hover and focus — only `transform` and `opacity` are animated, so there is still zero layout shift.
* Reveal threshold lowered to ~300px for a snappier feel.
* Motion logic moved to a real enqueued, deferred asset (`assets/js/motion.js`) loaded in the footer — no inline script blobs.
* Progressive enhancement: the button now renders visible and fully usable without JS; the script takes over visibility once it loads.
* Hardened reduced-motion support: the script reacts live to OS changes and the stylesheet disables the ring/hover/reveal transitions under `prefers-reduced-motion: reduce`.

= 1.1.0 =
* Stylesheet now ships as a real, cache-busted asset file instead of inline CSS.
* Added dark-theme support via prefers-color-scheme so the button stays visible on dark sites.
* Added a subtle fade-and-scale reveal animation (still honors prefers-reduced-motion).
* Button now respects mobile safe-area insets and shrinks slightly on small screens to avoid overlapping content.
* Added an `sbtt_is_active` filter so themes can opt the button in or out per view.
* Tightened scroll-position detection (uses document.scrollingElement) and hardened markup escaping.

= 1.0.0 =
* Initial release.
