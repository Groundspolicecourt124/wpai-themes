<?php
/**
 * Sidebar template.
 *
 * @package Monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}

// Close the main column opened in header.php and flag it for footer.php.
$GLOBALS['monolith_main_closed'] = true;
?>
	</div><!-- .content-main -->
	<aside class="widget-area" aria-label="<?php esc_attr_e( 'Sidebar', 'monolith' ); ?>">
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	</aside>
