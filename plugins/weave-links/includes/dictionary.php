<?php
/**
 * Title dictionary for Weave — Auto Internal Links.
 *
 * Builds and caches the map of linkable phrases to permalinks that the linker
 * walks for each post. The dictionary is the only "expensive" work the plugin
 * does, so it is computed once, cached in a transient, and rebuilt eagerly when
 * a post is saved or deleted.
 *
 * Two sources feed the dictionary:
 *   1. Every published post's title (the automatic web of internal links).
 *   2. An optional developer keyword => URL map supplied via the
 *      `weave_dictionary` filter (custom anchors to anywhere).
 *
 * @package Weave
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Resolve the minimum eligible title length, honoring the filter.
 *
 * Falls back to the default for any non-positive value, so a misbehaving filter
 * can never make every one-character substring linkable.
 *
 * @return int Minimum title length in characters, always >= 1.
 */
function weave_min_title_length() {
	/**
	 * Filter the minimum length (in characters) a post title must have to be
	 * eligible for auto-linking. Short, generic titles match too aggressively,
	 * so anything below this is skipped.
	 *
	 * @param int $length Default minimum title length.
	 */
	$length = (int) apply_filters( 'weave_min_title_length', WEAVE_MIN_TITLE_LENGTH );

	return $length > 0 ? $length : WEAVE_MIN_TITLE_LENGTH;
}

/**
 * Build the raw phrase => target map from published post titles.
 *
 * Tuned for speed: pulls only the columns we need (ID + title) straight from the
 * database in a single query, skips revisions/attachments, and never primes the
 * meta or term caches. Titles are decoded and normalized so the matcher compares
 * against the same text a reader sees.
 *
 * @return array<string,array{id:int,url:string}> Map of normalized title => target.
 */
function weave_build_post_dictionary() {
	global $wpdb;

	$dictionary = array();

	// One lean query: published posts only, just the ID and title. We cap the
	// result generously so a colossal site can't blow up memory; the longest,
	// most specific titles are the valuable ones and ordering by length keeps
	// them. 5000 titles is far more than any single post could ever link to.
	$rows = $wpdb->get_results(
		"SELECT ID, post_title
		 FROM {$wpdb->posts}
		 WHERE post_type = 'post'
		   AND post_status = 'publish'
		   AND post_title != ''
		 ORDER BY CHAR_LENGTH( post_title ) DESC
		 LIMIT 5000"
	);

	if ( empty( $rows ) ) {
		return $dictionary;
	}

	$min_length = weave_min_title_length();

	foreach ( $rows as $row ) {
		$id = (int) $row->ID;

		// Strip any stray tags and collapse whitespace to a single space.
		$title     = wp_strip_all_tags( (string) $row->post_title );
		$collapsed = preg_replace( '/\s+/u', ' ', $title );
		// preg_replace returns null on a PCRE error (e.g. malformed UTF-8); fall
		// back to the un-collapsed title so trim() is never handed null (a
		// deprecation on PHP 8.1+).
		$title = trim( null === $collapsed ? $title : $collapsed );

		if ( '' === $title ) {
			continue;
		}

		// Skip titles containing HTML-sensitive characters. In rendered content
		// these are entity-encoded and texturized (& => &amp;, ' => &#8217;, etc.),
		// so a raw-title match against the finished HTML would be unreliable and
		// could corrupt markup. Plain-text titles (the overwhelming majority) are
		// matched safely; the rare title with these characters simply isn't a
		// matchable phrase, which is the safe choice.
		if ( preg_match( '/[<>&"\']/', $title ) ) {
			continue;
		}

		// Length gate (counts characters, not bytes, when mbstring is present).
		$len = function_exists( 'mb_strlen' ) ? mb_strlen( $title ) : strlen( $title );
		if ( $len < $min_length ) {
			continue;
		}

		$permalink = get_permalink( $id );
		if ( ! $permalink ) {
			continue;
		}

		// Key on a case-folded title so duplicates collapse; keep the first
		// (longest, thanks to the ORDER BY) winner for a given text.
		$key = function_exists( 'mb_strtolower' ) ? mb_strtolower( $title ) : strtolower( $title );

		if ( isset( $dictionary[ $key ] ) ) {
			continue;
		}

		$dictionary[ $key ] = array(
			'id'  => $id,
			'url' => $permalink,
		);
	}

	return $dictionary;
}

/**
 * Get the full linkable dictionary, served from a transient when available.
 *
 * Shape: a list of entries, each `array( phrase, lower, id, url )`, ordered
 * longest-phrase-first so the matcher always prefers the most specific title
 * (e.g. "WordPress Security" wins over "WordPress"). The post-title map is
 * cached; the developer `weave_dictionary` filter is merged in fresh on every
 * call so a filter change takes effect immediately without a cache flush.
 *
 * @return array<int,array{phrase:string,lower:string,id:int,url:string}>
 */
function weave_get_dictionary() {
	$cached = get_transient( WEAVE_DICTIONARY_KEY );

	if ( is_array( $cached ) ) {
		$post_map = $cached;
	} else {
		$post_map = weave_build_post_dictionary();
		// Cache even an empty map (as an array) so a content-less site does not
		// re-query on every page view; the sentinel is simply an empty array.
		set_transient( WEAVE_DICTIONARY_KEY, $post_map, WEAVE_DICTIONARY_TTL );
	}

	// Normalize the cached post map into entries.
	$entries = array();
	foreach ( $post_map as $lower => $target ) {
		// Recover a display phrase from the lowercased key; the matcher is
		// case-insensitive so the canonical lower form is all we need to search.
		$entries[] = array(
			'phrase' => (string) $lower,
			'lower'  => (string) $lower,
			'id'     => (int) $target['id'],
			'url'    => (string) $target['url'],
		);
	}

	/**
	 * Filter the developer keyword => URL map merged into the dictionary.
	 *
	 * Each key is a phrase to match (case-insensitive, whole-word) and each value
	 * is the destination URL. These entries can target anything — pages, taxonomy
	 * archives, external resources — and they take precedence over post titles
	 * with the same text. Example:
	 *
	 *     add_filter( 'weave_dictionary', function ( $map ) {
	 *         $map['affiliate marketing'] = home_url( '/guides/affiliate/' );
	 *         return $map;
	 *     } );
	 *
	 * @param array<string,string> $map Phrase => URL map (default empty).
	 */
	$custom = apply_filters( 'weave_dictionary', array() );

	if ( is_array( $custom ) && ! empty( $custom ) ) {
		$seen_custom = array();

		foreach ( $custom as $phrase => $url ) {
			$phrase = trim( wp_strip_all_tags( (string) $phrase ) );
			$url    = esc_url_raw( (string) $url );

			if ( '' === $phrase || '' === $url ) {
				continue;
			}

			$lower = function_exists( 'mb_strtolower' ) ? mb_strtolower( $phrase ) : strtolower( $phrase );

			if ( isset( $seen_custom[ $lower ] ) ) {
				continue;
			}
			$seen_custom[ $lower ] = true;

			// Custom entries win: drop any post-title entry with the same text so
			// the developer map always takes precedence.
			foreach ( $entries as $index => $entry ) {
				if ( $entry['lower'] === $lower ) {
					unset( $entries[ $index ] );
				}
			}

			$entries[] = array(
				'phrase' => $phrase,
				'lower'  => $lower,
				'id'     => 0, // No source post; never self-link-suppressed.
				'url'    => $url,
			);
		}

		$entries = array_values( $entries );
	}

	// Order longest phrase first so specific titles beat the generic substrings
	// they contain. Use character length so multibyte titles sort correctly.
	usort(
		$entries,
		static function ( $a, $b ) {
			$la = function_exists( 'mb_strlen' ) ? mb_strlen( $a['lower'] ) : strlen( $a['lower'] );
			$lb = function_exists( 'mb_strlen' ) ? mb_strlen( $b['lower'] ) : strlen( $b['lower'] );

			return $lb <=> $la;
		}
	);

	return $entries;
}

/**
 * Rebuild the cached dictionary when content changes.
 *
 * Fired on save_post and deleted_post. We delete the transient and immediately
 * rebuild it so the very next request is already warm rather than paying the
 * rebuild cost on a visitor's page load. Revisions and autosaves are ignored —
 * they never change the published title set.
 *
 * @param int $post_id The post being saved or deleted.
 * @return void
 */
function weave_clear_dictionary( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	delete_transient( WEAVE_DICTIONARY_KEY );

	// Warm the cache eagerly with the fresh post map (filters are merged at read
	// time, so we only need to recompute and store the post-title portion).
	set_transient( WEAVE_DICTIONARY_KEY, weave_build_post_dictionary(), WEAVE_DICTIONARY_TTL );
}
add_action( 'save_post', 'weave_clear_dictionary' );
add_action( 'deleted_post', 'weave_clear_dictionary' );
