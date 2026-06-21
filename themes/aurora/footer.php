<?php
/**
 * Footer template.
 *
 * @package Aurora
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</div><!-- .site-wrap -->
</main><!-- .site-main -->

<footer class="site-footer">
	<div class="site-wrap site-footer__inner">
		<p class="site-footer__brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
		</p>

		<?php if ( has_nav_menu( 'social' ) ) : ?>
			<nav class="footer-nav" aria-label="<?php esc_attr_e( 'Footer navigation', 'aurora' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'social',
					'menu_class'     => 'footer-nav__list',
					'container'      => false,
					'fallback_cb'    => false,
					'depth'          => 1,
				) );
				?>
			</nav>
		<?php endif; ?>

		<p class="site-footer__credit">
			<?php
			printf(
				/* translators: 1: year, 2: site name */
				esc_html__( '© %1$s %2$s', 'aurora' ),
				esc_html( gmdate( 'Y' ) ),
				esc_html( get_bloginfo( 'name' ) )
			);
			?>
			<span class="site-footer__sep" aria-hidden="true">&middot;</span>
			<?php esc_html_e( 'Set in Aurora', 'aurora' ); ?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
