=== Smooth Back to Top ===
Contributors: wpaithemes
Tags: back to top, scroll, accessibility, button, navigation
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight, accessible floating button that appears on scroll and smoothly returns to the top.

== Description ==

Smooth Back to Top adds a single, unobtrusive floating button to the bottom-right
corner of your site. It fades in after the visitor scrolls down about 400 pixels and
smoothly scrolls the page back to the top when clicked.

It is built to be polite and accessible:

* Rendered as a native `<button>`, so it is fully keyboard-focusable.
* Carries a descriptive `aria-label` for screen readers.
* Respects the `prefers-reduced-motion` setting — no animation for users who ask for none.
* Moves focus to the top of the page after scrolling, so keyboard users keep their place.

It works on any theme, adds no settings page, makes no external requests, and stores no data.
The styles are added inline and the script is a tiny, dependency-free vanilla JavaScript file
loaded in the footer.

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

== Changelog ==

= 1.0.0 =
* Initial release.
