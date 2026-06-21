<?php
/**
 * 404 template.
 *
 * @package Sonnet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="error-404">
	<p class="big">404</p>
	<h1 class="entry-title"><?php esc_html_e( 'Page not found', 'sonnet' ); ?></h1>
	<p><?php esc_html_e( 'This page has slipped between the lines. Try a search:', 'sonnet' ); ?></p>
	<?php get_search_form(); ?>
</section>
<?php
get_footer();
