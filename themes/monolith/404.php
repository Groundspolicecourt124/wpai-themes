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
	<p class="single-kicker"><?php esc_html_e( 'Off the grid', 'monolith' ); ?></p>
	<h1 class="entry-title"><?php esc_html_e( 'Page not found', 'monolith' ); ?></h1>
	<p><?php esc_html_e( 'This page does not exist. The grid does not break — but this link did. Search, or return to the front.', 'monolith' ); ?></p>
	<?php get_search_form(); ?>
	<p style="margin-top:1.6rem;">
		<a class="read-more" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'Back to home', 'monolith' ); ?> <span aria-hidden="true">→</span>
		</a>
	</p>
</section>
<?php
get_footer();
