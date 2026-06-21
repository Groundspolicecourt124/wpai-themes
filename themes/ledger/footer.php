<?php
/**
 * Footer template.
 *
 * @package Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</div><!-- .content-area -->
</main><!-- .site-main -->

<footer class="site-footer">
	<div class="site-wrap">
		<p class="site-footer__brand"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
		<p>
			<?php
			printf(
				/* translators: 1: year, 2: site name */
				esc_html__( '© %1$s %2$s · Set in Ledger', 'ledger' ),
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
