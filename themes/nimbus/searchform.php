<?php
/**
 * Search form template.
 *
 * @package Nimbus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'nimbus' ); ?></span>
		<input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Search…', 'nimbus' ); ?>"
			value="<?php echo get_search_query(); ?>" name="s" />
	</label>
	<button type="submit" class="search-submit button"><?php esc_html_e( 'Search', 'nimbus' ); ?></button>
</form>
