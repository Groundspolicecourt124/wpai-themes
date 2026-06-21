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
	<p class="error-404__code" aria-hidden="true">404</p>
	<h1 class="error-404__title"><?php esc_html_e( 'This page slipped away', 'aurora' ); ?></h1>
	<p class="error-404__lead">
		<?php esc_html_e( 'The page you were looking for has moved, or perhaps never existed. Try a search, or head back to the journal.', 'aurora' ); ?>
	</p>

	<div class="error-404__search">
		<?php get_search_form(); ?>
	</div>

	<p class="error-404__home">
		<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'aurora' ); ?></a>
	</p>
</section>
<?php
get_footer();
