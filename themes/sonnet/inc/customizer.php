<?php
/**
 * Live theme customization: Colors & Style.
 *
 * Adds a small set of color controls to Appearance → Customize so a
 * non-technical user can recolor the whole theme without touching code.
 * Each control maps to a real CSS custom property defined in style.css and
 * updates live via postMessage (see assets/js/customizer-preview.js).
 *
 * @package Sonnet
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'sonnet_customize_defaults' ) ) {
	/**
	 * The default color values, kept in one place.
	 *
	 * These MUST match the :root tokens in style.css so the theme renders
	 * identically when no customizations have been made.
	 *
	 * @return array<string,string> Map of setting key (without prefix) to default hex.
	 */
	function sonnet_customize_defaults() {
		return array(
			'accent'  => '#cba35d', // --s-accent
			'bg'      => '#14161b', // --s-bg
			'surface' => '#1a1d24', // --s-surface
		);
	}
}

if ( ! function_exists( 'sonnet_customize_register' ) ) {
	/**
	 * Register the Customizer section, settings and controls.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
	 */
	function sonnet_customize_register( $wp_customize ) {
		$defaults = sonnet_customize_defaults();

		$wp_customize->add_section(
			'sonnet_colors',
			array(
				'title'    => esc_html__( 'Colors & Style', 'sonnet' ),
				'priority' => 30,
			)
		);

		// 1. Accent color → --s-accent (cascades to all derived gold shades).
		$wp_customize->add_setting(
			'sonnet_accent',
			array(
				'default'           => $defaults['accent'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'sonnet_accent',
				array(
					'label'       => esc_html__( 'Accent color', 'sonnet' ),
					'description' => esc_html__( 'Links, the gold ornament, and every accent shade follow this one color.', 'sonnet' ),
					'section'     => 'sonnet_colors',
					'settings'    => 'sonnet_accent',
				)
			)
		);

		// 2. Background color → --s-bg (the page canvas).
		$wp_customize->add_setting(
			'sonnet_bg',
			array(
				'default'           => $defaults['bg'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'sonnet_bg',
				array(
					'label'       => esc_html__( 'Background color', 'sonnet' ),
					'description' => esc_html__( 'The deep page canvas behind your writing.', 'sonnet' ),
					'section'     => 'sonnet_colors',
					'settings'    => 'sonnet_bg',
				)
			)
		);

		// 3. Surface color → --s-surface (cards, code blocks, the footer rail).
		$wp_customize->add_setting(
			'sonnet_surface',
			array(
				'default'           => $defaults['surface'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'sonnet_surface',
				array(
					'label'       => esc_html__( 'Surface color', 'sonnet' ),
					'description' => esc_html__( 'Raised panels: reading-list cards, code blocks, and the footer rail.', 'sonnet' ),
					'section'     => 'sonnet_colors',
					'settings'    => 'sonnet_surface',
				)
			)
		);

		// Live-update the site title and tagline without a full refresh.
		$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial(
				'blogname',
				array(
					'selector'        => '.site-title a',
					'render_callback' => 'sonnet_customize_partial_blogname',
				)
			);
			$wp_customize->selective_refresh->add_partial(
				'blogdescription',
				array(
					'selector'        => '.site-description',
					'render_callback' => 'sonnet_customize_partial_blogdescription',
				)
			);
		}
	}
}
add_action( 'customize_register', 'sonnet_customize_register' );

if ( ! function_exists( 'sonnet_customize_partial_blogname' ) ) {
	/**
	 * Render the site title for the selective-refresh partial.
	 *
	 * @return string
	 */
	function sonnet_customize_partial_blogname() {
		return get_bloginfo( 'name', 'display' );
	}
}

if ( ! function_exists( 'sonnet_customize_partial_blogdescription' ) ) {
	/**
	 * Render the site tagline for the selective-refresh partial.
	 *
	 * @return string
	 */
	function sonnet_customize_partial_blogdescription() {
		return get_bloginfo( 'description', 'display' );
	}
}

if ( ! function_exists( 'sonnet_customize_css' ) ) {
	/**
	 * Print the chosen colors as CSS custom properties on the front end.
	 *
	 * Only emits a variable when its saved value differs from the theme
	 * default, so an untouched site stays byte-for-byte on the stylesheet
	 * tokens. The derived accent shades are defined with color-mix() in
	 * style.css, so overriding only --s-accent cascades them automatically.
	 */
	function sonnet_customize_css() {
		$defaults = sonnet_customize_defaults();

		$accent  = get_theme_mod( 'sonnet_accent', $defaults['accent'] );
		$bg      = get_theme_mod( 'sonnet_bg', $defaults['bg'] );
		$surface = get_theme_mod( 'sonnet_surface', $defaults['surface'] );

		$rules = array();

		if ( $accent && $accent !== $defaults['accent'] ) {
			$rules[] = '--s-accent:' . $accent . ';';
		}
		if ( $bg && $bg !== $defaults['bg'] ) {
			$rules[] = '--s-bg:' . $bg . ';';
		}
		if ( $surface && $surface !== $defaults['surface'] ) {
			$rules[] = '--s-surface:' . $surface . ';';
		}

		if ( empty( $rules ) ) {
			return;
		}

		printf(
			'<style id="%1$s-customize">:root{%2$s}</style>' . "\n",
			esc_attr( get_template() ),
			esc_html( implode( '', $rules ) )
		);
	}
}
add_action( 'wp_head', 'sonnet_customize_css', 20 );

if ( ! function_exists( 'sonnet_customize_preview_js' ) ) {
	/**
	 * Enqueue the live-preview script inside the Customizer preview frame.
	 */
	function sonnet_customize_preview_js() {
		wp_enqueue_script(
			'sonnet-customizer-preview',
			get_template_directory_uri() . '/assets/js/customizer-preview.js',
			array( 'customize-preview' ),
			wp_get_theme()->get( 'Version' ),
			true
		);
	}
}
add_action( 'customize_preview_init', 'sonnet_customize_preview_js' );
