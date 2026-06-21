<?php
/**
 * Monolith Customizer — live color controls.
 *
 * Adds a "Colors & Style" section to Appearance → Customize with color
 * pickers for the accent, page background, and surface (panel) color.
 * Changes preview live (postMessage) and are output as CSS custom
 * properties on the front end so the whole theme retypes its palette.
 *
 * @package Monolith
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default palette values — must match the :root tokens in style.css so the
 * theme renders identically until the user changes something.
 */
if ( ! function_exists( 'monolith_color_defaults' ) ) {
	function monolith_color_defaults() {
		return array(
			'accent'  => '#00e5a0', // --m-accent
			'bg'      => '#0a0a0a', // --m-bg
			'surface' => '#121212', // --m-surface
		);
	}
}

/**
 * Register Customizer section, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize The Customizer manager.
 */
if ( ! function_exists( 'monolith_customize_register' ) ) {
	function monolith_customize_register( $wp_customize ) {
		$defaults = monolith_color_defaults();

		$wp_customize->add_section(
			'monolith_colors',
			array(
				'title'    => esc_html__( 'Colors & Style', 'monolith' ),
				'priority' => 30,
			)
		);

		// Accent color → --m-accent (and its derived hover/ink shades).
		$wp_customize->add_setting(
			'monolith_accent',
			array(
				'default'           => $defaults['accent'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'monolith_accent',
				array(
					'label'       => esc_html__( 'Accent color', 'monolith' ),
					'description' => esc_html__( 'The electric highlight on links, buttons, tags, and hovers.', 'monolith' ),
					'section'     => 'monolith_colors',
					'settings'    => 'monolith_accent',
				)
			)
		);

		// Background color → --m-bg (page canvas).
		$wp_customize->add_setting(
			'monolith_bg',
			array(
				'default'           => $defaults['bg'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'monolith_bg',
				array(
					'label'       => esc_html__( 'Background color', 'monolith' ),
					'description' => esc_html__( 'The page canvas behind everything.', 'monolith' ),
					'section'     => 'monolith_colors',
					'settings'    => 'monolith_bg',
				)
			)
		);

		// Surface color → --m-surface (cards, sidebar frames, blockquotes).
		$wp_customize->add_setting(
			'monolith_surface',
			array(
				'default'           => $defaults['surface'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'monolith_surface',
				array(
					'label'       => esc_html__( 'Surface color', 'monolith' ),
					'description' => esc_html__( 'Panels, cards, code blocks, and framed sidebar widgets.', 'monolith' ),
					'section'     => 'monolith_colors',
					'settings'    => 'monolith_surface',
				)
			)
		);

		// Live-update the site title and tagline.
		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
			$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

			$wp_customize->selective_refresh->add_partial(
				'blogname',
				array(
					'selector'        => '.site-title a',
					'render_callback' => 'monolith_customize_partial_blogname',
				)
			);
			$wp_customize->selective_refresh->add_partial(
				'blogdescription',
				array(
					'selector'        => '.site-description',
					'render_callback' => 'monolith_customize_partial_blogdescription',
				)
			);
		}
	}
}
add_action( 'customize_register', 'monolith_customize_register' );

/**
 * Render callback for the blogname partial.
 *
 * @return string Site title.
 */
if ( ! function_exists( 'monolith_customize_partial_blogname' ) ) {
	function monolith_customize_partial_blogname() {
		return get_bloginfo( 'name', 'display' );
	}
}

/**
 * Render callback for the blogdescription partial.
 *
 * @return string Site tagline.
 */
if ( ! function_exists( 'monolith_customize_partial_blogdescription' ) ) {
	function monolith_customize_partial_blogdescription() {
		return get_bloginfo( 'description', 'display' );
	}
}

/**
 * Print the chosen colors as CSS custom properties on the front end.
 *
 * Only emits a variable when the saved value differs from the theme default,
 * so an untouched install renders byte-for-byte identically to before.
 */
if ( ! function_exists( 'monolith_customize_css' ) ) {
	function monolith_customize_css() {
		$defaults = monolith_color_defaults();

		$accent  = sanitize_hex_color( get_theme_mod( 'monolith_accent', $defaults['accent'] ) );
		$bg      = sanitize_hex_color( get_theme_mod( 'monolith_bg', $defaults['bg'] ) );
		$surface = sanitize_hex_color( get_theme_mod( 'monolith_surface', $defaults['surface'] ) );

		$vars = array();

		if ( $accent && strtolower( $accent ) !== strtolower( $defaults['accent'] ) ) {
			$vars['--m-accent'] = $accent;
		}
		if ( $bg && strtolower( $bg ) !== strtolower( $defaults['bg'] ) ) {
			$vars['--m-bg'] = $bg;
		}
		if ( $surface && strtolower( $surface ) !== strtolower( $defaults['surface'] ) ) {
			$vars['--m-surface'] = $surface;
		}

		if ( empty( $vars ) ) {
			return;
		}

		$css = '';
		foreach ( $vars as $name => $value ) {
			$css .= $name . ': ' . $value . '; ';
		}

		printf(
			'<style id="monolith-customize">:root{ %s}</style>' . "\n",
			esc_html( trim( $css ) )
		);
	}
}
add_action( 'wp_head', 'monolith_customize_css', 20 );

/**
 * Enqueue the live-preview script inside the Customizer preview frame.
 */
if ( ! function_exists( 'monolith_customize_preview_js' ) ) {
	function monolith_customize_preview_js() {
		wp_enqueue_script(
			'monolith-customizer-preview',
			get_template_directory_uri() . '/assets/js/customizer-preview.js',
			array( 'customize-preview' ),
			wp_get_theme()->get( 'Version' ),
			true
		);
	}
}
add_action( 'customize_preview_init', 'monolith_customize_preview_js' );
