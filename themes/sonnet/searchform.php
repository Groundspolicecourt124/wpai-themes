<?php
/**
 * Search form template.
 *
 * @package Sonnet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sonnet_search_id = 'search-field-' . uniqid();
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $sonnet_search_id ); ?>"><?php esc_html_e( 'Search for:', 'sonnet' ); ?></label>
	<input
		type="search"
		id="<?php echo esc_attr( $sonnet_search_id ); ?>"
		class="search-field"
		placeholder="<?php esc_attr_e( 'Search the journal…', 'sonnet' ); ?>"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		name="s"
	/>
	<button type="submit" class="search-submit button"><?php esc_html_e( 'Search', 'sonnet' ); ?></button>
</form>
