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
	<p><?php esc_html_e( 'The page you were looking for has drifted off into the clouds. Try a search, or head back home.', 'nimbus' ); ?></p>
	<?php get_search_form(); ?>
	<a class="btn-ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'nimbus' ); ?></a>
</section>
<?php
get_sidebar();
get_footer();
