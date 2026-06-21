=== Reading Time Badge ===
Contributors: wpaithemes
Tags: reading time, posts, badge, content
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a tasteful "X min read" badge above the content of single posts. Zero configuration.

== Description ==

Reading Time Badge estimates how long a post takes to read (based on ~220 words per
minute) and shows a small, unobtrusive badge above the post content. It works on any
theme, adds no settings page, makes no external requests, and stores no data.

Developers can tune the reading speed:

`add_filter( 'rtb_words_per_minute', fn() => 250 );`

== Installation ==

1. In wp-admin go to Plugins > Add New > Upload Plugin.
2. Choose reading-time-badge.zip and click Install Now.
3. Click Activate. That's it — open any post to see the badge.

== Changelog ==

= 1.0.0 =
* Initial release.
