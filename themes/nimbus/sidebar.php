<?php
/**
 * Sidebar template. Closes the content column, then renders the widget area.
 *
 * @package Nimbus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
		</div><!-- .content-area -->
<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<aside class="widget-area" aria-label="<?php esc_attr_e( 'Sidebar', 'nimbus' ); ?>">
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
		</aside>
<?php endif; ?>
