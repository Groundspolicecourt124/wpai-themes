<?php
/**
 * 404 template.
 *
 * @package Monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="error-404">
	<p class="big">404</p>
	<h1 class="entry-title"><?php esc_html_e( 'Page not found', 'monolith' ); ?></h1>
	<p><?php esc_html_e( 'This page does not exist. The grid does not break — but this link did. Try a search:', 'monolith' ); ?></p>
	<?php get_search_form(); ?>
</section>
<?php
get_footer();
