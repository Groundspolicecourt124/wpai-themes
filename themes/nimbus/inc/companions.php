<?php
/**
 * Recommended companion plugins.
 *
 * Shows admins a one-click installer so the theme arrives "packaged" with its
 * free companion plugins, installed straight from the WPAI Themes site. Admin
 * only; adds nothing to the front end. Identical across all WPAI themes.
 *
 * @package WPAIThemes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpai_companions' ) ) {
	/**
	 * The free companion plugins, installed from the WPAI Themes site.
	 *
	 * @return array<string,array<string,string>>
	 */
	function wpai_companions() {
		$base = 'https://lordbasilaiassistant-sudo.github.io/wpai-themes/downloads/';

		return array(
			'beacon-ai-seo'      => array(
				'name' => 'Beacon — AI & SEO',
				'file' => 'beacon-ai-seo/beacon-ai-seo.php',
				'desc' => 'Automated SEO, social cards, and an llms.txt so AI agents can read your site.',
				'zip'  => $base . 'beacon-ai-seo.zip',
			),
			'contents-toc'       => array(
				'name' => 'Contents',
				'file' => 'contents-toc/contents-toc.php',
				'desc' => 'A smart table of contents for long posts.',
				'zip'  => $base . 'contents-toc.zip',
			),
			'kindred-related'    => array(
				'name' => 'Kindred',
				'file' => 'kindred-related/kindred-related.php',
				'desc' => 'Tasteful related posts that keep readers exploring.',
				'zip'  => $base . 'kindred-related.zip',
			),
			'reading-time-badge' => array(
				'name' => 'Reading Time Badge',
				'file' => 'reading-time-badge/reading-time-badge.php',
				'desc' => 'A tasteful "X min read" badge above your posts.',
				'zip'  => $base . 'reading-time-badge.zip',
			),
			'smooth-back-to-top' => array(
				'name' => 'Smooth Back to Top',
				'file' => 'smooth-back-to-top/smooth-back-to-top.php',
				'desc' => 'A floating scroll-progress "back to top" button.',
				'zip'  => $base . 'smooth-back-to-top.zip',
			),
		);
	}
}

if ( ! function_exists( 'wpai_companions_missing' ) ) {
	/**
	 * Companions that are not yet active.
	 *
	 * @return array<string,array<string,string>>
	 */
	function wpai_companions_missing() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$missing = array();
		foreach ( wpai_companions() as $slug => $plugin ) {
			if ( ! is_plugin_active( $plugin['file'] ) ) {
				$missing[ $slug ] = $plugin;
			}
		}

		return $missing;
	}
}

if ( ! function_exists( 'wpai_companions_notice' ) ) {
	/**
	 * Dismissible admin notice recommending the companion plugins.
	 *
	 * @return void
	 */
	function wpai_companions_notice() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}
		if ( get_user_meta( get_current_user_id(), 'wpai_companions_dismissed', true ) ) {
			return;
		}

		$missing = wpai_companions_missing();
		if ( empty( $missing ) ) {
			return;
		}

		$theme   = wp_get_theme()->get( 'Name' );
		$install = wp_nonce_url( admin_url( 'admin-post.php?action=wpai_install_companions' ), 'wpai_companions' );
		$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=wpai_dismiss_companions' ), 'wpai_companions' );

		echo '<div class="notice notice-info">';
		echo '<p><strong>' . esc_html( $theme ) . '</strong> ' . esc_html( 'works beautifully with its free companion plugins:' ) . '</p>';
		echo '<ul style="list-style:disc;margin-left:1.4em">';
		foreach ( $missing as $plugin ) {
			echo '<li><strong>' . esc_html( $plugin['name'] ) . '</strong> &mdash; ' . esc_html( $plugin['desc'] ) . '</li>';
		}
		echo '</ul>';
		echo '<p><a class="button button-primary" href="' . esc_url( $install ) . '">' . esc_html( 'Install &amp; activate all' ) . '</a> ';
		echo '<a class="button" href="' . esc_url( $dismiss ) . '">' . esc_html( 'Dismiss' ) . '</a></p>';
		echo '</div>';
	}
	add_action( 'admin_notices', 'wpai_companions_notice' );
}

if ( ! function_exists( 'wpai_companions_install' ) ) {
	/**
	 * One-click install + activate of every missing companion, from our zips.
	 *
	 * @return void
	 */
	function wpai_companions_install() {
		if ( ! current_user_can( 'install_plugins' ) || ! check_admin_referer( 'wpai_companions' ) ) {
			wp_die( esc_html( 'You are not allowed to install plugins.' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$installed = get_plugins();

		foreach ( wpai_companions() as $plugin ) {
			if ( is_plugin_active( $plugin['file'] ) ) {
				continue;
			}

			if ( ! isset( $installed[ $plugin['file'] ] ) ) {
				$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
				$upgrader->install( $plugin['zip'] );
			}

			if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin['file'] ) ) {
				activate_plugin( $plugin['file'] );
			}
		}

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'plugins.php' ) );
		exit;
	}
	add_action( 'admin_post_wpai_install_companions', 'wpai_companions_install' );
}

if ( ! function_exists( 'wpai_companions_dismiss' ) ) {
	/**
	 * Remember that this admin dismissed the recommendation.
	 *
	 * @return void
	 */
	function wpai_companions_dismiss() {
		if ( ! check_admin_referer( 'wpai_companions' ) ) {
			wp_die( esc_html( 'Invalid request.' ) );
		}
		update_user_meta( get_current_user_id(), 'wpai_companions_dismissed', 1 );
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
		exit;
	}
	add_action( 'admin_post_wpai_dismiss_companions', 'wpai_companions_dismiss' );
}
