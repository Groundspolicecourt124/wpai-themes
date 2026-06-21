<?php
/**
 * 404 template.
 *
 * @package Aurora
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="error-404">
	<p class="big">404</p>
	<h1 class="entry-title"><?php esc_html_e( 'Page not found', 'aurora' ); ?></h1>
	<p><?php esc_html_e( 'The page you were looking for has wandered off. Try a search:', 'aurora' ); ?></p>
	<?php get_search_form(); ?>
</section>
<?php
get_footer();
