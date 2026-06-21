<?php
/**
 * 404 template.
 *
 * @package Verdant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="primary-content">
	<section class="error-404">
		<p class="big">404</p>
		<h1 class="entry-title"><?php esc_html_e( 'Page not found', 'verdant' ); ?></h1>
		<p><?php esc_html_e( 'The page you were looking for has wandered off into the garden. Try a search:', 'verdant' ); ?></p>
		<?php get_search_form(); ?>
	</section>
</div><!-- .primary-content -->
<?php
get_footer();
