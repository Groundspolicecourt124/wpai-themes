<?php
/**
 * Layer 2 — backfill still-empty alt on the front end.
 *
 * Layer 1 covers everything uploaded after activation, but a real site already
 * has thousands of images from before. This layer makes those accessible too,
 * at render time, without ever modifying the database:
 *
 *   A) wp_get_attachment_image_attributes — fills alt for images rendered by
 *      WordPress (featured images, galleries, the image block, get_*_image()).
 *   B) the_content pass — fills alt on inline <img> tags in post content that
 *      have no alt (or an empty alt that wasn't intentionally decorative).
 *
 * Both paths reuse describe_alt_derive(), so the front-end value matches what
 * Layer 1 would have written. A human's existing alt is always left untouched.
 *
 * @package DescribeAlt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Whether Layer 2 should run for the current request.
 *
 * Front-of-site views only — never the admin, feeds, or REST/AJAX, where the
 * raw stored value is the correct thing to expose. Filterable.
 *
 * @return bool
 */
function describe_alt_frontend_active() {
	$active = ! is_admin()
		&& ! is_feed()
		&& ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		&& ! ( defined( 'DOING_AJAX' ) && DOING_AJAX );

	/**
	 * Filter whether Describe backfills alt on the front end for this request.
	 *
	 * @param bool $active Whether the front-end backfill should run.
	 */
	return (bool) apply_filters( 'describe_alt_frontend_active', $active );
}

/**
 * Fill empty alt on WordPress-generated image markup.
 *
 * Hooks `wp_get_attachment_image_attributes`, which feeds every image rendered
 * via wp_get_attachment_image() and friends (featured images, image blocks,
 * galleries). We only act when alt is missing or empty — an intentionally
 * decorative image (explicit alt="") that another component set is respected,
 * because by the time this fires that component has already chosen ''.
 *
 * To keep this allocation-free on the hot path we read the stored alt first
 * (a single, cached postmeta lookup) and only derive when it's empty.
 *
 * @param array        $attr       The image tag attributes.
 * @param WP_Post|null $attachment The attachment post, if available.
 * @return array Filtered attributes.
 */
function describe_alt_filter_attributes( $attr, $attachment = null ) {
	if ( ! describe_alt_frontend_active() ) {
		return $attr;
	}

	// Already has a non-empty alt (stored or set by a prior filter): respect it.
	if ( isset( $attr['alt'] ) && '' !== trim( (string) $attr['alt'] ) ) {
		return $attr;
	}

	if ( ! $attachment instanceof WP_Post ) {
		return $attr;
	}

	$alt = describe_alt_derive( $attachment->ID );

	if ( '' !== $alt ) {
		$attr['alt'] = $alt; // wp_get_attachment_image() escapes attrs on output.
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'describe_alt_filter_attributes', 20, 2 );

/**
 * Backfill alt on inline <img> tags inside post content.
 *
 * Scans the_content for <img> tags that have no alt attribute, or an empty alt
 * that is NOT an intentional decorative marker, and fills it. We recognise a
 * WordPress-attached image by its `wp-image-{ID}` class (added by the editor)
 * so we can derive from the right attachment; tags without a resolvable ID are
 * left exactly as-is (we never fabricate alt for unknown images).
 *
 * Runs late (priority 20) so it sees the final, fully-rendered markup. The
 * regex is scoped to <img …> tags only and rewrites just the alt attribute, so
 * surrounding markup is preserved byte-for-byte.
 *
 * @param string $content The post content HTML.
 * @return string
 */
function describe_alt_filter_content( $content ) {
	if ( ! describe_alt_frontend_active() || '' === trim( (string) $content ) ) {
		return $content;
	}

	// Fast bail: no <img> at all, nothing to do.
	if ( false === stripos( $content, '<img' ) ) {
		return $content;
	}

	return preg_replace_callback(
		'/<img\b[^>]*>/i',
		'describe_alt_rewrite_img_tag',
		$content
	);
}
add_filter( 'the_content', 'describe_alt_filter_content', 20 );

/**
 * Rewrite a single <img> tag, adding alt when it is missing and derivable.
 *
 * Decision table:
 *   - alt present and non-empty  → leave untouched (human / prior value).
 *   - alt present but empty ("") → treat as intentionally decorative, leave it.
 *   - alt absent entirely        → derive from the wp-image-{ID} attachment and
 *                                   inject alt; if no ID or nothing meaningful,
 *                                   leave the tag exactly as-is.
 *
 * Treating an explicit empty alt as decorative is deliberate and correct: an
 * author (or the block editor's "mark as decorative") wrote alt="" on purpose,
 * and overriding it would make screen readers announce a now-redundant image.
 *
 * @param array $match Regex match; $match[0] is the full <img …> tag.
 * @return string The original or rewritten tag.
 */
function describe_alt_rewrite_img_tag( $match ) {
	$tag = $match[0];

	// An alt attribute is present (empty or not, quoted or unquoted) → respect
	// the author's choice and never risk injecting a second alt. Detecting the
	// attribute by its name alone (rather than parsing its value) is what keeps
	// this safe: a value-parsing pattern can miss an alt whose value is unquoted
	// or contains a ">" (the tag match having been truncated at that ">"), and
	// would then wrongly inject a duplicate alt. The leading \s prevents matching
	// foreign attributes such as data-alt=.
	if ( preg_match( '/\salt\s*=/i', $tag ) ) {
		return $tag;
	}

	// No alt attribute. Try to resolve the attachment via wp-image-{ID}.
	if ( ! preg_match( '/wp-image-(\d+)/', $tag, $id_match ) ) {
		return $tag; // Unknown image — never fabricate alt for it.
	}

	$attachment_id = (int) $id_match[1];
	$alt           = describe_alt_derive( $attachment_id );

	if ( '' === $alt ) {
		return $tag;
	}

	// Inject alt right after "<img" so attribute order stays predictable.
	return preg_replace(
		'/<img\b/i',
		'<img alt="' . esc_attr( $alt ) . '"',
		$tag,
		1
	);
}
