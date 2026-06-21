<?php
/**
 * Footer template.
 *
 * @package Verdant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</div><!-- .content-grid -->
</main><!-- .site-main -->

<footer class="site-footer">
	<div class="site-wrap">
		<p>
			<?php
			printf(
				/* translators: 1: year, 2: site name */
				esc_html__( '© %1$s %2$s. Grown with Verdant.', 'verdant' ),
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
