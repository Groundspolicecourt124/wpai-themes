<?php
/**
 * Sidebar template.
 *
 * @package Verdant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>
<aside class="widget-area" aria-label="<?php esc_attr_e( 'Sidebar', 'verdant' ); ?>">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
