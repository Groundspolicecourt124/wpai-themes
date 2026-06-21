<?php
/**
 * Shared helpers for Beacon — AI & SEO.
 *
 * Small, side-effect-free utilities used by the meta-tag, structured-data, and
 * /llms.txt modules: context detection, value extraction (title, description,
 * canonical, image), and the best-effort "another SEO plugin is active" guard.
 *
 * @package BeaconAiSeo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Whether Beacon should emit its <head> output for the current request.
 *
 * Bails in the admin, on feeds, on embeds, in the REST/AJAX context, and — by
 * default — when a well-known SEO plugin is already managing the head, so we
 * never produce duplicate meta tags or competing JSON-LD. All of this is
 * filterable via `beacon_output_head`.
 *
 * @return bool
 */
function beacon_should_output_head() {
	$output = ! is_admin()
		&& ! is_feed()
		&& ! is_embed()
		&& ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		&& ! ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		&& ! beacon_other_seo_plugin_active();

	/**
	 * Filter whether Beacon prints its meta tags and JSON-LD on this request.
	 *
	 * Returning false lets a site defer entirely to another SEO solution while
	 * keeping the /llms.txt feature (which is governed separately).
	 *
	 * @param bool $output Whether the head output should run.
	 */
	return (bool) apply_filters( 'beacon_output_head', $output );
}

/**
 * Best-effort detection of another active SEO plugin.
 *
 * Checks for the marquee constants/classes/functions defined by the popular
 * SEO plugins. This is intentionally conservative: a false negative just means
 * Beacon and another plugin both emit tags (filterable off); a false positive
 * is avoided by only matching very specific, stable symbols.
 *
 * The result is filterable so a site can force Beacon on or off regardless.
 *
 * @return bool True if a known SEO plugin appears to be active.
 */
function beacon_other_seo_plugin_active() {
	$detected = defined( 'WPSEO_VERSION' )                 // Yoast SEO.
		|| defined( 'RANK_MATH_VERSION' )                  // Rank Math.
		|| defined( 'AIOSEO_VERSION' )                     // All in One SEO.
		|| defined( 'SEOPRESS_VERSION' )                   // SEOPress.
		|| defined( 'THE_SEO_FRAMEWORK_VERSION' )          // The SEO Framework.
		|| class_exists( 'WPSEO_Frontend', false )
		|| function_exists( 'rank_math' )
		|| function_exists( 'aioseo' )
		|| function_exists( 'the_seo_framework' );

	/**
	 * Filter whether another SEO plugin is considered active.
	 *
	 * @param bool $detected Result of the built-in detection.
	 */
	return (bool) apply_filters( 'beacon_other_seo_plugin_active', $detected );
}

/**
 * The site name, sanitized for output.
 *
 * @return string
 */
function beacon_site_name() {
	return wp_strip_all_tags( get_bloginfo( 'name' ) );
}

/**
 * The site tagline (description), sanitized for output.
 *
 * @return string
 */
function beacon_site_tagline() {
	return wp_strip_all_tags( get_bloginfo( 'description' ) );
}

/**
 * Resolve the queried object's title for meta/OG use.
 *
 * Falls back through singular titles, archive titles, search and 404 strings,
 * and finally the site name, so there is always something sensible.
 *
 * @return string Plain-text title (unescaped; escape at output).
 */
function beacon_get_title() {
	$title = '';

	if ( is_singular() ) {
		$title = get_the_title( get_queried_object_id() );
	} elseif ( is_front_page() || is_home() ) {
		$title = beacon_site_name();
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$title = single_term_title( '', false );
	} elseif ( is_author() ) {
		$author = get_queried_object();
		$title  = $author instanceof WP_User ? $author->display_name : '';
	} elseif ( is_post_type_archive() ) {
		$title = post_type_archive_title( '', false );
	} elseif ( is_search() ) {
		/* translators: %s: search query. Raw query; escaped at output. */
		$title = sprintf( __( 'Search results for "%s"', 'beacon-ai-seo' ), get_search_query( false ) );
	} elseif ( is_404() ) {
		$title = __( 'Page not found', 'beacon-ai-seo' );
	} elseif ( is_archive() ) {
		$title = get_the_archive_title();
	}

	$title = wp_strip_all_tags( (string) $title );

	if ( '' === $title ) {
		$title = beacon_site_name();
	}

	/**
	 * Filter the resolved document title used in meta tags and JSON-LD.
	 *
	 * @param string $title The computed title.
	 */
	return (string) apply_filters( 'beacon_title', $title );
}

/**
 * Resolve a meta description for the current view.
 *
 * Prefers a hand-written excerpt, then a trimmed version of the content, then
 * the term/author description, and finally the site tagline. The result is a
 * single line of plain text, length-capped on a word boundary.
 *
 * @return string Plain-text description (unescaped; escape at output).
 */
function beacon_get_description() {
	$description = '';

	if ( is_singular() ) {
		$post = get_post( get_queried_object_id() );

		if ( $post instanceof WP_Post ) {
			if ( '' !== trim( (string) $post->post_excerpt ) ) {
				$description = $post->post_excerpt;
			} else {
				$description = $post->post_content;
			}
		}
	} elseif ( is_front_page() || is_home() ) {
		$description = beacon_site_tagline();
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$description = $term->description;
		}
	} elseif ( is_author() ) {
		$author = get_queried_object();
		if ( $author instanceof WP_User ) {
			$description = get_the_author_meta( 'description', $author->ID );
		}
	}

	$description = beacon_normalize_text( $description );

	if ( '' === $description ) {
		$description = beacon_normalize_text( beacon_site_tagline() );
	}

	$description = beacon_trim_words( $description, 30, 160 );

	/**
	 * Filter the resolved meta description.
	 *
	 * @param string $description The computed description.
	 */
	return (string) apply_filters( 'beacon_description', $description );
}

/**
 * Collapse rich content to a single clean line of plain text.
 *
 * Runs shortcodes off, strips tags, decodes entities, and squeezes whitespace
 * so descriptions never leak markup or shortcodes.
 *
 * @param string $text Raw text/content.
 * @return string
 */
function beacon_normalize_text( $text ) {
	$text = strip_shortcodes( (string) $text );
	$text = wp_strip_all_tags( $text );
	$text = html_entity_decode( $text, ENT_QUOTES, get_bloginfo( 'charset' ) ?: 'UTF-8' );
	$text = preg_replace( '/\s+/u', ' ', $text );

	return trim( (string) $text );
}

/**
 * Trim plain text to at most $max_words words and $max_chars characters.
 *
 * Cuts on a word boundary and appends a horizontal ellipsis only when the text
 * was actually shortened.
 *
 * @param string $text      Plain text (already normalized).
 * @param int    $max_words Maximum number of words.
 * @param int    $max_chars Hard character ceiling.
 * @return string
 */
function beacon_trim_words( $text, $max_words = 30, $max_chars = 160 ) {
	$text = (string) $text;

	if ( '' === $text ) {
		return '';
	}

	$words   = preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY );
	$trimmed = false;

	if ( is_array( $words ) && count( $words ) > $max_words ) {
		$words   = array_slice( $words, 0, $max_words );
		$trimmed = true;
	}

	$text = is_array( $words ) ? implode( ' ', $words ) : $text;

	if ( function_exists( 'mb_strlen' ) ? mb_strlen( $text ) > $max_chars : strlen( $text ) > $max_chars ) {
		$cut  = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, $max_chars ) : substr( $text, 0, $max_chars );
		$last = strrpos( $cut, ' ' );
		$text = false !== $last ? substr( $cut, 0, $last ) : $cut;
		$text = rtrim( $text, " \t\n\r\0\x0B.,;:" );

		$trimmed = true;
	}

	return $trimmed ? $text . "\xE2\x80\xA6" : $text;
}

/**
 * Build a one-line excerpt for a given post (used by /llms.txt and JSON-LD).
 *
 * @param int|WP_Post $post  Post or post ID.
 * @param int         $words Word cap.
 * @return string
 */
function beacon_post_summary( $post, $words = 25 ) {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	$raw = '' !== trim( (string) $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;

	return beacon_trim_words( beacon_normalize_text( $raw ), $words, 200 );
}

/**
 * The canonical URL for the current request.
 *
 * @return string Raw URL (escape with esc_url at output).
 */
function beacon_get_canonical() {
	$url = '';

	if ( is_singular() ) {
		$url = get_permalink( get_queried_object_id() );
	} elseif ( is_front_page() ) {
		$url = home_url( '/' );
	} elseif ( is_home() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		$url        = $posts_page > 0 ? get_permalink( $posts_page ) : '';
		if ( ! $url ) {
			$url = home_url( '/' );
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );
			$url  = is_wp_error( $link ) ? '' : $link;
		}
	} elseif ( is_author() ) {
		$url = get_author_posts_url( get_queried_object_id() );
	} elseif ( is_post_type_archive() ) {
		$pt  = get_query_var( 'post_type' );
		$pt  = is_array( $pt ) ? reset( $pt ) : $pt;
		$url = $pt ? get_post_type_archive_link( $pt ) : '';
	} elseif ( is_search() ) {
		$url = get_search_link();
	}

	if ( ! $url ) {
		// Fall back to the current request path only (query string stripped) so we
		// never reflect arbitrary user-supplied query args into the canonical/OG URL.
		$path = '';
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$raw  = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$path = (string) wp_parse_url( $raw, PHP_URL_PATH );
		}
		$url = home_url( '/' === $path || '' === $path ? '/' : $path );
	}

	/**
	 * Filter the canonical URL.
	 *
	 * @param string $url The computed canonical URL.
	 */
	return (string) apply_filters( 'beacon_canonical', $url );
}

/**
 * The best representative image URL for the current view.
 *
 * Uses the featured image on singular views, then the site icon, so Open Graph
 * and Twitter cards always have something to show when available.
 *
 * @return string Raw URL or '' when none is available (escape at output).
 */
function beacon_get_image() {
	$url = '';

	if ( is_singular() && has_post_thumbnail( get_queried_object_id() ) ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id( get_queried_object_id() ), 'full' );
		if ( is_array( $src ) && ! empty( $src[0] ) ) {
			$url = $src[0];
		}
	}

	if ( '' === $url ) {
		$icon = get_site_icon_url( 512 );
		if ( $icon ) {
			$url = $icon;
		}
	}

	/**
	 * Filter the representative image URL for meta tags and JSON-LD.
	 *
	 * @param string $url The computed image URL ('' if none).
	 */
	return (string) apply_filters( 'beacon_image', $url );
}

/**
 * The robots directive for the current view.
 *
 * Honors the site's "discourage search engines" setting and noindexes
 * low-value views (search results, paginated comment pages) by default.
 *
 * @return string e.g. "index, follow" or "noindex, follow".
 */
function beacon_get_robots() {
	$index = '0' !== get_option( 'blog_public', '1' ) ? 'index' : 'noindex';

	if ( is_search() || is_404() ) {
		$index = 'noindex';
	}

	$robots = $index . ', follow';

	/**
	 * Filter the robots meta directive.
	 *
	 * @param string $robots The computed robots value.
	 */
	return (string) apply_filters( 'beacon_robots', $robots );
}
