<?php
/**
 * Search form template.
 *
 * @package Aurora
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aurora_id = 'search-' . ( function_exists( 'wp_unique_id' ) ? wp_unique_id() : uniqid() );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $aurora_id ); ?>"><?php esc_html_e( 'Search for:', 'aurora' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $aurora_id ); ?>" class="search-field"
		placeholder="<?php esc_attr_e( 'Search the journal…', 'aurora' ); ?>"
		value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
	<button type="submit" class="search-submit button">
		<span class="screen-reader-text"><?php esc_html_e( 'Search', 'aurora' ); ?></span>
		<svg class="search-submit__icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
			<circle cx="10.5" cy="10.5" r="6.5" fill="none" stroke="currentColor" stroke-width="2" />
			<line x1="15.5" y1="15.5" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
		</svg>
	</button>
</form>
