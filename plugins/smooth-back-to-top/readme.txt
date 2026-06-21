=== Smooth Back to Top ===
Contributors: wpaithemes
Tags: back to top, scroll, accessibility, button, navigation
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight, accessible floating button that appears on scroll and smoothly returns to the top.

== Description ==

Smooth Back to Top adds a single, unobtrusive floating button to the bottom-right
corner of your site. It fades in after the visitor scrolls down about 400 pixels and
smoothly scrolls the page back to the top when clicked.

It is built to be polite and accessible:

* Rendered as a native `<button>`, so it is fully keyboard-focusable and operable.
* Carries a descriptive `aria-label` and a crisp upward-chevron icon.
* Fades and scales in once you scroll past about 400 pixels — no layout shift, ever.
* Respects the `prefers-reduced-motion` setting — it jumps instead of animating for users who ask for no motion.
* Adapts to both light and dark themes via `prefers-color-scheme`.
* Keeps clear of mobile safe-area insets (such as the iOS home indicator).
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

= 1.1.0 =
* Stylesheet now ships as a real, cache-busted asset file instead of inline CSS.
* Added dark-theme support via prefers-color-scheme so the button stays visible on dark sites.
* Added a subtle fade-and-scale reveal animation (still honors prefers-reduced-motion).
* Button now respects mobile safe-area insets and shrinks slightly on small screens to avoid overlapping content.
* Added an `sbtt_is_active` filter so themes can opt the button in or out per view.
* Tightened scroll-position detection (uses document.scrollingElement) and hardened markup escaping.

= 1.0.0 =
* Initial release.
