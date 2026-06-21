<?php
/**
 * 404 template.
 *
 * @package Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="site-wrap content-area">
	<div class="site-content">
		<section class="error-404">
			<p class="big">404</p>
			<h1 class="entry-title"><?php esc_html_e( 'Page not found', 'ledger' ); ?></h1>
			<p><?php esc_html_e( 'This story has gone to press elsewhere. Try a search:', 'ledger' ); ?></p>
			<?php get_search_form(); ?>
		</section>
	</div><!-- .site-content -->
<?php
get_footer();
