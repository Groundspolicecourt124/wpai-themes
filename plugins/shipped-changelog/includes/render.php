<?php
/**
 * Render layer for Shipped — Auto Changelog & Roadmap.
 *
 * Builds the accessible, theme-adaptive HTML for the changelog timeline and the
 * roadmap status board. Every dynamic value is escaped on output. Two sources
 * are supported and merge seamlessly:
 *   1. Auto: posts in the watched "Changelog" / "Roadmap" categories.
 *   2. Manual: a simple inner-shortcode-content convention, one entry per line.
 *
 * @package Shipped
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Build a one-line, plain-text summary for a post (timeline/board body).
 *
 * Prefers a hand-written excerpt, else a trimmed version of the content, with
 * shortcodes and markup stripped so the card body never leaks HTML.
 *
 * @param int $post_id The entry post.
 * @param int $words   Word cap.
 * @return string Plain text (escape at output).
 */
function shipped_entry_summary( $post_id, $words = 40 ) {
	$post = get_post( (int) $post_id );

	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	$raw = '' !== trim( (string) $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;
	$charset = get_bloginfo( 'charset' );
	$charset = '' !== (string) $charset ? $charset : 'UTF-8';

	$raw = strip_shortcodes( (string) $raw );
	$raw = wp_strip_all_tags( $raw );
	$raw = html_entity_decode( $raw, ENT_QUOTES, $charset );
	$raw = preg_replace( '/\s+/u', ' ', $raw );
	$raw = trim( (string) $raw );

	if ( '' === $raw ) {
		return '';
	}

	$parts = preg_split( '/\s+/u', $raw, -1, PREG_SPLIT_NO_EMPTY );
	if ( is_array( $parts ) && count( $parts ) > $words ) {
		$raw = implode( ' ', array_slice( $parts, 0, $words ) ) . "\xE2\x80\xA6";
	}

	return $raw;
}

/**
 * A unique-enough DOM id fragment for ARIA wiring.
 *
 * Increments a static counter so multiple shortcodes on one page never collide.
 *
 * @param string $prefix Short prefix (e.g. 'shipped-cl').
 * @return string
 */
function shipped_unique_id( $prefix ) {
	static $n = 0;
	++$n;

	return sanitize_html_class( $prefix ) . '-' . $n;
}

/**
 * Parse manual inner-shortcode content into a list of entry rows.
 *
 * Convention (one entry per line; fields separated by a pipe `|`):
 *
 *   2024-06-01 | v1.2.0 | Title here | Optional description
 *
 * For the roadmap, the second field is the status (planned|in-progress|shipped)
 * instead of a version:
 *
 *   in-progress | Realtime sync | Optional description
 *
 * Lines are tolerant: missing trailing fields are simply empty. Blank lines and
 * lines beginning with `#` (comments) are skipped. The first field is always
 * treated as the date for changelog mode and the status for roadmap mode.
 *
 * @param string $content Raw inner shortcode content (already shortcode-unwrapped by WP).
 * @param string $mode    'changelog' or 'roadmap'.
 * @return array<int,array<string,string>> Parsed rows.
 */
function shipped_parse_inner_content( $content, $mode ) {
	$content = trim( (string) $content );

	if ( '' === $content ) {
		return array();
	}

	// WP passes inner content with <br>/<p> when autop runs inside shortcodes;
	// normalize those to newlines before splitting.
	$content = preg_replace( '#<\s*br\s*/?\s*>#i', "\n", $content );
	$content = preg_replace( '#</\s*p\s*>#i', "\n", $content );
	$content = wp_strip_all_tags( $content );

	$charset = get_bloginfo( 'charset' );
	$charset = '' !== (string) $charset ? $charset : 'UTF-8';
	$content = html_entity_decode( $content, ENT_QUOTES, $charset );

	$rows  = array();
	$lines = preg_split( '/\r\n|\r|\n/', $content );

	foreach ( (array) $lines as $line ) {
		$line = trim( $line );

		if ( '' === $line || 0 === strpos( $line, '#' ) ) {
			continue;
		}

		$fields = array_map( 'trim', explode( '|', $line ) );

		if ( 'roadmap' === $mode ) {
			$rows[] = array(
				'status' => isset( $fields[0] ) ? $fields[0] : '',
				'title'  => isset( $fields[1] ) ? $fields[1] : '',
				'body'   => isset( $fields[2] ) ? $fields[2] : '',
			);
		} else {
			$rows[] = array(
				'date'    => isset( $fields[0] ) ? $fields[0] : '',
				'version' => isset( $fields[1] ) ? $fields[1] : '',
				'title'   => isset( $fields[2] ) ? $fields[2] : '',
				'body'    => isset( $fields[3] ) ? $fields[3] : '',
			);
		}
	}

	return $rows;
}

/**
 * Normalize a free-text version field from manual content to a "v"-label.
 *
 * Accepts "1.2.0", "v1.2.0", or "" and returns a normalized label or ''.
 *
 * @param string $version Raw version text.
 * @return string
 */
function shipped_normalize_version( $version ) {
	$version = trim( (string) $version );

	if ( '' === $version ) {
		return '';
	}

	if ( preg_match( '/^v?(\d+(?:\.\d+)+(?:[-.][0-9a-z]+)*)$/i', $version, $m ) ) {
		return 'v' . $m[1];
	}

	// Allow short labels like "v2" too, but require it to start with a digit/v.
	if ( preg_match( '/^v?\d/i', $version ) ) {
		return ( 'v' === strtolower( substr( $version, 0, 1 ) ) ) ? $version : 'v' . $version;
	}

	return '';
}

/**
 * Build one timeline entry's HTML (a single <li> on the vertical timeline).
 *
 * @param array{date_iso:string,date_human:string,version:string,title:string,body:string,permalink:string} $entry Entry data.
 * @return string Safe HTML, or '' when there is nothing to show.
 */
function shipped_render_timeline_entry( $entry ) {
	$title = '' !== $entry['title'] ? $entry['title'] : __( '(untitled)', 'shipped-changelog' );

	// Title is a link when we have a permalink (auto entries), plain text otherwise.
	if ( '' !== $entry['permalink'] ) {
		$title_html = sprintf(
			'<a class="shipped-tl__link" href="%1$s">%2$s</a>',
			esc_url( $entry['permalink'] ),
			esc_html( $title )
		);
	} else {
		$title_html = esc_html( $title );
	}

	$version_html = '';
	if ( '' !== $entry['version'] ) {
		$version_html = sprintf(
			'<span class="shipped-tl__version">%s</span>',
			esc_html( $entry['version'] )
		);
	}

	$date_html = '';
	if ( '' !== $entry['date_human'] ) {
		// Only emit a machine-readable <time datetime> when we actually parsed a
		// date; a free-text manual date (no valid ISO value) is shown as plain
		// text so we never output an invalid empty datetime attribute.
		if ( '' !== $entry['date_iso'] ) {
			$date_html = sprintf(
				'<time class="shipped-tl__date" datetime="%1$s">%2$s</time>',
				esc_attr( $entry['date_iso'] ),
				esc_html( $entry['date_human'] )
			);
		} else {
			$date_html = sprintf(
				'<span class="shipped-tl__date">%s</span>',
				esc_html( $entry['date_human'] )
			);
		}
	}

	$body_html = '';
	if ( '' !== $entry['body'] ) {
		$body_html = sprintf(
			'<p class="shipped-tl__body">%s</p>',
			esc_html( $entry['body'] )
		);
	}

	return sprintf(
		'<li class="shipped-tl__entry" data-shipped-reveal>' .
			'<span class="shipped-tl__marker" aria-hidden="true"></span>' .
			'<div class="shipped-tl__card">' .
				'<div class="shipped-tl__meta">%1$s%2$s</div>' .
				'<h3 class="shipped-tl__title">%3$s</h3>' .
				'%4$s' .
			'</div>' .
		'</li>',
		$date_html,    // Escaped above.
		$version_html, // Escaped above.
		$title_html,   // Escaped above.
		$body_html     // Escaped above.
	);
}

/**
 * Assemble the changelog entry list (auto from category + manual rows).
 *
 * Auto entries come first (newest published first); manual rows follow in the
 * order written. Returns an array of normalized entry arrays ready for
 * shipped_render_timeline_entry().
 *
 * @param string $category    Category to source auto entries from.
 * @param array  $manual_rows Parsed manual rows (see shipped_parse_inner_content).
 * @return array<int,array<string,string>>
 */
function shipped_collect_changelog_entries( $category, $manual_rows ) {
	$entries = array();

	foreach ( shipped_get_entry_ids( 'changelog', $category ) as $id ) {
		$permalink = get_permalink( $id );

		$entries[] = array(
			'date_iso'   => get_the_date( 'c', $id ),
			'date_human' => get_the_date( '', $id ),
			'version'    => shipped_post_version( $id ),
			'title'      => get_the_title( $id ),
			'body'       => shipped_entry_summary( $id ),
			'permalink'  => $permalink ? $permalink : '',
		);
	}

	foreach ( (array) $manual_rows as $row ) {
		$ts = '' !== $row['date'] ? strtotime( $row['date'] ) : false;

		$entries[] = array(
			'date_iso'   => $ts ? gmdate( 'c', $ts ) : '',
			'date_human' => $ts ? date_i18n( (string) get_option( 'date_format', 'F j, Y' ), $ts ) : $row['date'],
			'version'    => shipped_normalize_version( $row['version'] ),
			'title'      => $row['title'],
			'body'       => $row['body'],
			'permalink'  => '',
		);
	}

	return $entries;
}

/**
 * Render the full changelog timeline section.
 *
 * @param string $category    Category to source from.
 * @param array  $manual_rows Parsed manual rows.
 * @param string $heading     Section heading text.
 * @param bool   $schema      Whether to emit JSON-LD structured data.
 * @return string Safe section HTML, or an empty-state message.
 */
function shipped_render_changelog( $category, $manual_rows, $heading, $schema ) {
	$entries = shipped_collect_changelog_entries( $category, $manual_rows );

	$heading_id = shipped_unique_id( 'shipped-cl-h' );

	if ( empty( $entries ) ) {
		return shipped_empty_state(
			$heading_id,
			$heading,
			/* translators: %s: category name. */
			sprintf( __( 'No changelog entries yet. Publish a post in the "%s" category to get started.', 'shipped-changelog' ), $category )
		);
	}

	$items = '';
	foreach ( $entries as $entry ) {
		$items .= shipped_render_timeline_entry( $entry );
	}

	$json_ld = $schema ? shipped_changelog_jsonld( $entries ) : '';

	return sprintf(
		'<section class="shipped shipped-changelog" aria-labelledby="%1$s" data-shipped>' .
			'<h2 class="shipped__heading" id="%1$s">%2$s</h2>' .
			'<ol class="shipped-tl" role="list">%3$s</ol>' .
		'</section>%4$s',
		esc_attr( $heading_id ),
		esc_html( $heading ),
		$items,   // Each entry escaped in shipped_render_timeline_entry().
		$json_ld  // Escaped JSON in shipped_changelog_jsonld().
	);
}

/**
 * Assemble roadmap items bucketed into the status columns.
 *
 * Returns the status map with a 'items' array added to each column, each item
 * being a normalized array { title, body, permalink }.
 *
 * @param string $category    Category to source auto items from.
 * @param array  $manual_rows Parsed manual roadmap rows.
 * @return array<string,array{label:string,items:array<int,array<string,string>>}>
 */
function shipped_collect_roadmap_items( $category, $manual_rows ) {
	$statuses = shipped_roadmap_statuses();

	$board = array();
	foreach ( $statuses as $key => $status ) {
		$board[ $key ] = array(
			'label' => isset( $status['label'] ) ? (string) $status['label'] : (string) $key,
			'items' => array(),
		);
	}

	// Auto items from the category, bucketed by their status tag/term.
	foreach ( shipped_get_entry_ids( 'roadmap', $category ) as $id ) {
		$key = shipped_post_status_key( $id, $statuses );
		if ( ! isset( $board[ $key ] ) ) {
			continue;
		}

		$permalink = get_permalink( $id );

		$board[ $key ]['items'][] = array(
			'title'     => get_the_title( $id ),
			'body'      => shipped_entry_summary( $id, 28 ),
			'permalink' => $permalink ? $permalink : '',
		);
	}

	// Manual roadmap rows: map the status field to a column key by its slugs.
	$first = (string) array_key_first( $board );
	foreach ( (array) $manual_rows as $row ) {
		$key = shipped_match_status_slug( $row['status'], $statuses );
		$key = isset( $board[ $key ] ) ? $key : $first;

		$board[ $key ]['items'][] = array(
			'title'     => $row['title'],
			'body'      => $row['body'],
			'permalink' => '',
		);
	}

	return $board;
}

/**
 * Match a free-text status string from manual content to a column key.
 *
 * @param string $status   Raw status text (e.g. "in progress").
 * @param array  $statuses The status map.
 * @return string Column key (defaults to the first column).
 */
function shipped_match_status_slug( $status, $statuses ) {
	$slug = sanitize_title( (string) $status );

	foreach ( $statuses as $key => $def ) {
		$accepted = array_map( 'sanitize_title', (array) $def['slugs'] );
		if ( in_array( $slug, $accepted, true ) || sanitize_title( (string) $key ) === $slug ) {
			return (string) $key;
		}
	}

	$first = array_key_first( $statuses );

	return null !== $first ? (string) $first : 'planned';
}

/**
 * Render one roadmap column's items as a list.
 *
 * @param array $items Column items.
 * @return string Safe HTML (an empty-column note when there are none).
 */
function shipped_render_roadmap_items( $items ) {
	if ( empty( $items ) ) {
		return '<li class="shipped-rm__empty">' . esc_html__( 'Nothing here yet.', 'shipped-changelog' ) . '</li>';
	}

	$html = '';
	foreach ( $items as $item ) {
		$title = '' !== $item['title'] ? $item['title'] : __( '(untitled)', 'shipped-changelog' );

		if ( '' !== $item['permalink'] ) {
			$title_html = sprintf(
				'<a class="shipped-rm__link" href="%1$s">%2$s</a>',
				esc_url( $item['permalink'] ),
				esc_html( $title )
			);
		} else {
			$title_html = esc_html( $title );
		}

		$body_html = '' !== $item['body']
			? sprintf( '<p class="shipped-rm__body">%s</p>', esc_html( $item['body'] ) )
			: '';

		$html .= sprintf(
			'<li class="shipped-rm__item" data-shipped-reveal>' .
				'<h4 class="shipped-rm__title">%1$s</h4>%2$s' .
			'</li>',
			$title_html,
			$body_html
		);
	}

	return $html;
}

/**
 * Render the full roadmap status board section.
 *
 * @param string $category    Category to source from.
 * @param array  $manual_rows Parsed manual rows.
 * @param string $heading     Section heading text.
 * @return string Safe section HTML, or an empty-state message.
 */
function shipped_render_roadmap( $category, $manual_rows, $heading ) {
	$board = shipped_collect_roadmap_items( $category, $manual_rows );

	$heading_id = shipped_unique_id( 'shipped-rm-h' );

	// Empty when every column is empty.
	$has_any = false;
	foreach ( $board as $column ) {
		if ( ! empty( $column['items'] ) ) {
			$has_any = true;
			break;
		}
	}

	if ( ! $has_any ) {
		return shipped_empty_state(
			$heading_id,
			$heading,
			/* translators: %s: category name. */
			sprintf( __( 'No roadmap items yet. Publish posts in the "%s" category and tag them planned, in-progress, or shipped.', 'shipped-changelog' ), $category )
		);
	}

	$columns = '';
	$col_i   = 0;
	foreach ( $board as $key => $column ) {
		++$col_i;
		$col_head_id = $heading_id . '-c' . $col_i;
		$count       = count( $column['items'] );

		$columns .= sprintf(
			'<li class="shipped-rm__column shipped-rm__column--%1$s" role="listitem">' .
				'<div class="shipped-rm__col" role="group" aria-labelledby="%2$s">' .
					'<h3 class="shipped-rm__colhead" id="%2$s">' .
						'<span class="shipped-rm__dot" aria-hidden="true"></span>' .
						'<span class="shipped-rm__collabel">%3$s</span>' .
						'<span class="shipped-rm__count">%4$d</span>' .
					'</h3>' .
					'<ul class="shipped-rm__list" role="list">%5$s</ul>' .
				'</div>' .
			'</li>',
			esc_attr( sanitize_html_class( (string) $key ) ),
			esc_attr( $col_head_id ),
			esc_html( $column['label'] ),
			(int) $count,
			shipped_render_roadmap_items( $column['items'] ) // Escaped within.
		);
	}

	return sprintf(
		'<section class="shipped shipped-roadmap" aria-labelledby="%1$s" data-shipped>' .
			'<h2 class="shipped__heading" id="%1$s">%2$s</h2>' .
			'<ul class="shipped-rm__board" role="list">%3$s</ul>' .
		'</section>',
		esc_attr( $heading_id ),
		esc_html( $heading ),
		$columns // Each column escaped above.
	);
}

/**
 * Render a graceful empty-state section.
 *
 * Shown (instead of nothing) when a shortcode is placed but the source category
 * has no entries yet — so an author dropping the shortcode immediately sees
 * exactly what to do, rather than a blank space they assume is broken.
 *
 * @param string $heading_id ARIA id for the heading.
 * @param string $heading    Section heading.
 * @param string $message    Guidance message.
 * @return string Safe HTML.
 */
function shipped_empty_state( $heading_id, $heading, $message ) {
	return sprintf(
		'<section class="shipped shipped--empty" aria-labelledby="%1$s" data-shipped>' .
			'<h2 class="shipped__heading" id="%1$s">%2$s</h2>' .
			'<p class="shipped__empty">%3$s</p>' .
		'</section>',
		esc_attr( $heading_id ),
		esc_html( $heading ),
		esc_html( $message )
	);
}

/**
 * Build minimal JSON-LD for the changelog as a schema.org ItemList.
 *
 * Emits a compact, valid ItemList where each entry is a ListItem naming the
 * release. Optional and filterable off via the shortcode's schema attribute.
 * The JSON is encoded safely and printed inside a type="application/ld+json"
 * script; values are escaped for that context.
 *
 * @param array $entries Collected changelog entries.
 * @return string The <script> tag, or '' when nothing to emit.
 */
function shipped_changelog_jsonld( $entries ) {
	if ( empty( $entries ) ) {
		return '';
	}

	$list = array();
	$pos  = 0;

	foreach ( $entries as $entry ) {
		++$pos;

		$name = '' !== $entry['version']
			? $entry['version'] . ' — ' . $entry['title']
			: $entry['title'];

		$item = array(
			'@type'    => 'ListItem',
			'position' => $pos,
			'name'     => $name,
		);

		if ( '' !== $entry['permalink'] ) {
			$item['url'] = $entry['permalink'];
		}

		$list[] = $item;

		if ( $pos >= 100 ) {
			break; // Keep the payload small and crawler-friendly.
		}
	}

	$data = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'ItemList',
		'name'            => wp_strip_all_tags( get_bloginfo( 'name' ) ) . ' — ' . __( 'Changelog', 'shipped-changelog' ),
		'itemListOrder'   => 'https://schema.org/ItemListOrderDescending',
		'numberOfItems'   => count( $list ),
		'itemListElement' => $list,
	);

	$json = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

	if ( false === $json ) {
		return '';
	}

	// Neutralize any "</script" sequence so the payload can't break out of the
	// tag. Replacing "<" with its JSON < escape keeps the JSON valid while
	// making a literal closing tag impossible to inject.
	$json = str_replace( '<', '\u' . '003C', $json );

	return '<script type="application/ld+json">' . $json . '</script>';
}
