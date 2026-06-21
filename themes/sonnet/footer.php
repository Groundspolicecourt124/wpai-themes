<?php
/**
 * Footer template.
 *
 * @package Sonnet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</div><!-- .site-wrap -->
</main><!-- .site-main -->

<footer class="site-footer">
	<div class="site-wrap site-footer__inner">
		<p class="site-footer__mark"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
		<p class="site-footer__colophon">
			<?php
			printf(
				/* translators: 1: current year, 2: site name. */
				esc_html__( '© %1$s %2$s.', 'sonnet' ),
				esc_html( gmdate( 'Y' ) ),
				esc_html( get_bloginfo( 'name' ) )
			);
			?>
			<span class="site-footer__sep" aria-hidden="true">·</span>
			<span class="site-footer__credit"><?php esc_html_e( 'Set in Sonnet.', 'sonnet' ); ?></span>
			<a class="site-footer__top" href="#content"><?php esc_html_e( 'Back to top &uarr;', 'sonnet' ); ?></a>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
