<?php
/**
 * Footer template.
 *
 * @package Nimbus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</div><!-- .site-layout -->
</main><!-- .site-main -->

<footer class="site-footer">
	<div class="site-wrap site-footer__inner">
		<span class="site-footer__brand"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
		<p class="site-footer__copy">
			<?php
			printf(
				/* translators: 1: year, 2: site name */
				esc_html__( '© %1$s %2$s · Built with Nimbus.', 'nimbus' ),
				esc_html( gmdate( 'Y' ) ),
				esc_html( get_bloginfo( 'name' ) )
			);
			?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
