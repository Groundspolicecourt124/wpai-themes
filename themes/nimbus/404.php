<?php
/**
 * 404 template.
 *
 * @package Nimbus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="error-404">
	<p class="big">404</p>
	<h1 class="entry-title"><?php esc_html_e( 'Page not found', 'nimbus' ); ?></h1>
	<p><?php esc_html_e( 'The page you were looking for has drifted off. Try a search:', 'nimbus' ); ?></p>
	<?php get_search_form(); ?>
</section>
<?php
get_footer();
