<?php
/**
 * Footer template.
 *
 * @package Monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Close the main column if the sidebar template did not already do so.
if ( empty( $GLOBALS['monolith_main_closed'] ) ) :
	?>
	</div><!-- .content-main -->
	<?php
endif;
$GLOBALS['monolith_main_closed'] = false;
?>
	</div><!-- .content-grid -->
</main><!-- .site-main -->

<footer class="site-footer">
	<div class="site-wrap site-footer__inner">
		<p class="footer-mark">MONO<span>/</span>LITH</p>
		<p>
			<?php
			printf(
				/* translators: 1: year, 2: site name */
				esc_html__( '© %1$s %2$s — Built with Monolith.', 'monolith' ),
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
