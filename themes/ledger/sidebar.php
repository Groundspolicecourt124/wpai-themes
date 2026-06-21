<?php
/**
 * Sidebar template. Closes .site-content and renders the widget area.
 *
 * @package Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
		</div><!-- .site-content -->
<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<aside class="widget-area" aria-label="<?php esc_attr_e( 'Sidebar', 'ledger' ); ?>">
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
		</aside>
<?php endif; ?>
