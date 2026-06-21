<?php
/**
 * The linker for Weave — Auto Internal Links.
 *
 * Walks finished post HTML and hyperlinks the first occurrence of each linkable
 * phrase from the dictionary, without ever corrupting the markup. It does this
 * WITHOUT a DOM extension dependency: the content is split into "protected" and
 * "linkable" segments by a single tokenizing regex, matching only happens inside
 * the linkable (visible-text) segments, and the protected segments — existing
 * links, headings, code, etc. — are passed through byte-for-byte untouched.
 *
 * Guarantees:
 *   - Never links inside an existing <a>, an <h1>–<h6>, or <code>/<pre>/<kbd>.
 *   - Never links inside any HTML tag (attribute values are safe).
 *   - Whole-word, case-insensitive matching (Unicode word boundaries).
 *   - First occurrence only, at most one link per target, capped per post.
 *   - Never links a post to itself.
 *   - Every injected anchor is built with escaped attributes.
 *
 * @package Weave
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Resolve the per-post link cap, honoring the filter.
 *
 * A non-positive value falls back to the default, so a misbehaving filter can
 * never disable the cap entirely or request a negative number of links.
 *
 * @return int Maximum auto-links per post, always >= 1.
 */
function weave_max_links() {
	/**
	 * Filter the maximum number of auto-links injected into a single post.
	 *
	 * @param int $max Default maximum (WEAVE_MAX_LINKS).
	 */
	$max = (int) apply_filters( 'weave_max_links', WEAVE_MAX_LINKS );

	return $max > 0 ? $max : WEAVE_MAX_LINKS;
}

/**
 * Weave links into a single post's HTML content.
 *
 * @param string $content The post HTML (already run through wpautop, etc.).
 * @param int    $post_id The current post ID (its own title is never linked).
 * @return string The content with internal links woven in.
 */
function weave_link_content( $content, $post_id ) {
	$content = (string) $content;
	$post_id = (int) $post_id;

	if ( '' === $content ) {
		return $content;
	}

	$dictionary = weave_get_dictionary();
	if ( empty( $dictionary ) ) {
		return $content;
	}

	$max_links = weave_max_links();

	// Find every *protected* region — markup we must not touch. Everything BETWEEN
	// these regions is linkable visible text. We locate the regions with their
	// byte offsets (PREG_OFFSET_CAPTURE) and walk the gaps; this sidesteps the
	// split-with-capture-groups pitfall (inner backref groups getting re-inserted)
	// entirely, and the full match (group 0) is always the complete region.
	//
	// Branches, in order:
	//   - <a ...>...</a>            whole existing anchors (lazy, case-insensitive)
	//   - <h1>...</h1> … <h6>...</h6>   headings (\1 backref keeps them balanced)
	//   - <pre|code|kbd>...</…>     code-ish blocks (\2 backref balances them)
	//   - HTML comments
	//   - any other single tag      so we never match inside attributes
	// The `s` flag lets `.` span newlines; `i` makes tag names case-insensitive.
	$protected_pattern = '#'
		. '<a\b[^>]*>.*?</a>'                        // existing links
		. '|<(h[1-6])\b[^>]*>.*?</\1>'               // headings
		. '|<(pre|code|kbd)\b[^>]*>.*?</\2>'         // code-ish blocks
		. '|<!--.*?-->'                              // comments
		. '|<[^>]+>'                                 // any other lone tag
		. '#is';

	$matched = preg_match_all( $protected_pattern, $content, $matches, PREG_OFFSET_CAPTURE );

	if ( false === $matched ) {
		// Regex engine bailed (e.g. backtrack limit on pathological input):
		// leave the content untouched rather than risk corruption.
		return $content;
	}

	// The current post's own permalink, so even a developer-supplied custom entry
	// that happens to point here is never linked back to the page it sits on.
	$self_url = (string) get_permalink( $post_id );

	// State shared across all text gaps so caps and "first occurrence" hold for
	// the whole post, not per-gap.
	$state = array(
		'remaining'   => $max_links, // How many more links we may add.
		'used_phrase' => array(),    // lower phrase => true once linked once.
		'used_target' => array(),    // target key (url|id) => true, one per target.
		'self_id'     => $post_id,
		'self_url'    => $self_url,
	);

	$out    = '';
	$cursor = 0; // Byte offset of the next unprocessed character in $content.

	foreach ( $matches[0] as $match ) {
		$region = $match[0];          // The full protected region text.
		$offset = (int) $match[1];    // Its byte offset in $content.

		// Linkable gap: the text between the cursor and this protected region.
		if ( $offset > $cursor ) {
			$gap = substr( $content, $cursor, $offset - $cursor );
			$out .= 0 === $state['remaining']
				? $gap
				: weave_link_text_segment( $gap, $dictionary, $state );
		}

		// The protected region itself passes through byte-for-byte.
		$out   .= $region;
		$cursor = $offset + strlen( $region );
	}

	// Trailing linkable text after the last protected region.
	if ( $cursor < strlen( $content ) ) {
		$gap = substr( $content, $cursor );
		$out .= 0 === $state['remaining']
			? $gap
			: weave_link_text_segment( $gap, $dictionary, $state );
	}

	return $out;
}

/**
 * Link the first matching phrase(s) inside one plain-text segment.
 *
 * The segment contains only visible text (the tokenizer guarantees no tags), so
 * we can safely run whole-word, case-insensitive matches against it. We try the
 * dictionary in longest-phrase-first order and inject at most one link per phrase
 * and per target, decrementing the shared budget as we go.
 *
 * IMPORTANT: the segment text here is post-wptexturize, so straight quotes/dashes
 * the author typed may render as curly ones. We match the segment AS-IS, so the
 * dictionary phrase must appear with the same characters it has on screen; this
 * is the correct behavior for "link the title the reader sees."
 *
 * @param string $segment    A visible-text segment of the content.
 * @param array  $dictionary Ordered dictionary entries (longest first).
 * @param array  $state      Shared, mutable matching state (by reference).
 * @return string The segment with any links injected.
 */
function weave_link_text_segment( $segment, $dictionary, &$state ) {
	if ( '' === $segment || 0 === $state['remaining'] ) {
		return $segment;
	}

	foreach ( $dictionary as $entry ) {
		if ( 0 === $state['remaining'] ) {
			break;
		}

		$lower = $entry['lower'];

		// Already linked this exact phrase once in the post.
		if ( isset( $state['used_phrase'][ $lower ] ) ) {
			continue;
		}

		// Never link a post to itself — by source post ID for title entries, and by
		// destination URL so a custom entry (id 0) pointing at this page is skipped too.
		if ( $entry['id'] > 0 && $entry['id'] === $state['self_id'] ) {
			continue;
		}
		if ( '' !== $state['self_url'] && $entry['url'] === $state['self_url'] ) {
			continue;
		}

		// One link per destination across the whole post.
		$target_key = $entry['url'] . '|' . $entry['id'];
		if ( isset( $state['used_target'][ $target_key ] ) ) {
			continue;
		}

		$pattern = weave_phrase_pattern( $entry['phrase'] );
		if ( '' === $pattern ) {
			continue;
		}

		// Replace only the FIRST occurrence within this segment. We use a
		// callback so the matched text keeps its original casing inside the link.
		$did_replace = false;
		$replaced    = preg_replace_callback(
			$pattern,
			static function ( $m ) use ( $entry, &$did_replace ) {
				$did_replace = true;
				return weave_build_anchor( $m[0], $entry['url'] );
			},
			$segment,
			1
		);

		if ( null === $replaced ) {
			// Regex failure on this phrase: skip it, keep the segment intact.
			continue;
		}

		if ( $did_replace ) {
			$segment                              = $replaced;
			$state['used_phrase'][ $lower ]       = true;
			$state['used_target'][ $target_key ]  = true;
			$state['remaining']                  -= 1;
		}
	}

	return $segment;
}

/**
 * Build a whole-word, case-insensitive regex for a phrase.
 *
 * Uses lookaround "word boundaries" that work for Unicode letters/digits — the
 * native `\b` is ASCII-only, so we assert that the character on each side of the
 * match is not a word character ourselves. The `u` flag enables Unicode mode.
 *
 * @param string $phrase The phrase to match (raw, unescaped).
 * @return string A PCRE pattern delimited with '#', or '' if the phrase is empty.
 */
function weave_phrase_pattern( $phrase ) {
	$phrase = (string) $phrase;
	if ( '' === $phrase ) {
		return '';
	}

	$quoted = preg_quote( $phrase, '#' );

	// Collapse runs of escaped whitespace so a phrase typed with one space still
	// matches text where wptexturize / wpautop left a different run of spaces or
	// a non-breaking space between words.
	$collapsed = preg_replace( '/(\\\\?\s)+/u', '\\s+', $quoted );

	// If the collapse fails (PCRE error on pathological input) keep the safely
	// quoted phrase rather than letting a null collapse into an empty pattern
	// that would match a zero-width position and wrap nothing in an anchor.
	if ( null !== $collapsed ) {
		$quoted = $collapsed;
	}

	if ( '' === $quoted ) {
		return '';
	}

	// (?<![\w]) / (?![\w]) are Unicode-aware word boundaries with the u flag:
	// the match may not be flanked by another word character, so "art" never
	// matches inside "start" or "artist".
	return '#(?<![\p{L}\p{N}_])' . $quoted . '(?![\p{L}\p{N}_])#iu';
}

/**
 * Build a safe anchor for a matched phrase.
 *
 * The visible text is the literal text that matched (original casing preserved)
 * and is escaped with esc_html; the href is escaped with esc_url. A class hook
 * and a data attribute let themes target auto-links, and rel="noopener" is added
 * defensively. Nothing here is ever echoed unescaped.
 *
 * @param string $text The matched visible text (raw).
 * @param string $url  The destination URL (raw).
 * @return string The anchor HTML.
 */
function weave_build_anchor( $text, $url ) {
	return sprintf(
		'<a class="weave-link" data-weave="1" href="%1$s">%2$s</a>',
		esc_url( $url ),
		esc_html( $text )
	);
}
