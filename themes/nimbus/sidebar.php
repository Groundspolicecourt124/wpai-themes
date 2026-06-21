<?php
/**
 * Sidebar template.
 *
 * @package Nimbus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>
<aside class="widget-area" aria-label="<?php esc_attr_e( 'Sidebar', 'nimbus' ); ?>">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
