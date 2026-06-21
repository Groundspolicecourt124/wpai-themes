<?php
/**
 * Ledger Customizer: live color & style controls.
 *
 * Adds a "Colors & Style" section with color controls that drive the theme's
 * CSS custom properties, with instant (postMessage) live preview.
 *
 * @package Ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default values, kept in one place so the register, output, and reset logic
 * all agree. These match the original :root values in style.css exactly, so an
 * untouched site renders identically to before.
 *
 * @return array
 */
if ( ! function_exists( 'ledger_color_defaults' ) ) {
	function ledger_color_defaults() {
		return array(
			'ledger_accent'  => '#a4001a', // --l-accent
			'ledger_bg'      => '#faf8f4', // --l-paper
			'ledger_surface' => '#fffdf9', // --l-surface
		);
	}
}

/**
 * Register Customizer settings, the "Colors & Style" section, and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
if ( ! function_exists( 'ledger_customize_register' ) ) {
	function ledger_customize_register( $wp_customize ) {
		$defaults = ledger_color_defaults();

		// Make the core title & tagline update live (no full refresh).
		$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial( 'blogname', array(
				'selector'        => '.site-title a',
				'render_callback' => 'ledger_partial_blogname',
			) );
			$wp_customize->selective_refresh->add_partial( 'blogdescription', array(
				'selector'        => '.site-description',
				'render_callback' => 'ledger_partial_blogdescription',
			) );
		}

		// Colors & Style section.
		$wp_customize->add_section( 'ledger_colors', array(
			'title'    => esc_html__( 'Colors & Style', 'ledger' ),
			'priority' => 30,
		) );

		// Accent color -> --l-accent (and derived shades via color-mix).
		$wp_customize->add_setting( 'ledger_accent', array(
			'default'           => $defaults['ledger_accent'],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ledger_accent', array(
			'label'       => esc_html__( 'Accent color', 'ledger' ),
			'description' => esc_html__( 'The single editorial accent used for links, kickers, and highlights.', 'ledger' ),
			'section'     => 'ledger_colors',
			'settings'    => 'ledger_accent',
		) ) );

		// Background (page canvas) color -> --l-paper.
		$wp_customize->add_setting( 'ledger_bg', array(
			'default'           => $defaults['ledger_bg'],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ledger_bg', array(
			'label'       => esc_html__( 'Background color', 'ledger' ),
			'description' => esc_html__( 'The paper-like page canvas behind all content.', 'ledger' ),
			'section'     => 'ledger_colors',
			'settings'    => 'ledger_bg',
		) ) );

		// Surface color -> --l-surface (masthead and card surface).
		$wp_customize->add_setting( 'ledger_surface', array(
			'default'           => $defaults['ledger_surface'],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'ledger_surface', array(
			'label'       => esc_html__( 'Surface color', 'ledger' ),
			'description' => esc_html__( 'The lighter paper surface used for the masthead and story cards.', 'ledger' ),
			'section'     => 'ledger_colors',
			'settings'    => 'ledger_surface',
		) ) );
	}
}
add_action( 'customize_register', 'ledger_customize_register' );

/**
 * Selective-refresh render callback for the site title.
 *
 * @return void
 */
if ( ! function_exists( 'ledger_partial_blogname' ) ) {
	function ledger_partial_blogname() {
		bloginfo( 'name' );
	}
}

/**
 * Selective-refresh render callback for the site description.
 *
 * @return void
 */
if ( ! function_exists( 'ledger_partial_blogdescription' ) ) {
	function ledger_partial_blogdescription() {
		bloginfo( 'description' );
	}
}

/**
 * Print the customized CSS custom properties in the document head.
 *
 * Only emits a variable when its theme mod actually differs from the default,
 * so an untouched site outputs nothing extra and renders identically to before.
 *
 * @return void
 */
if ( ! function_exists( 'ledger_customize_css' ) ) {
	function ledger_customize_css() {
		$defaults = ledger_color_defaults();

		$accent  = get_theme_mod( 'ledger_accent', $defaults['ledger_accent'] );
		$bg      = get_theme_mod( 'ledger_bg', $defaults['ledger_bg'] );
		$surface = get_theme_mod( 'ledger_surface', $defaults['ledger_surface'] );

		$vars = array();

		if ( $accent && $accent !== $defaults['ledger_accent'] ) {
			// Derived shades follow --l-accent via color-mix in style.css.
			$vars['--l-accent'] = $accent;
		}
		if ( $bg && $bg !== $defaults['ledger_bg'] ) {
			$vars['--l-paper'] = $bg;
		}
		if ( $surface && $surface !== $defaults['ledger_surface'] ) {
			$vars['--l-surface'] = $surface;
		}

		if ( empty( $vars ) ) {
			return;
		}

		$declarations = '';
		foreach ( $vars as $name => $value ) {
			$declarations .= esc_html( $name ) . ':' . esc_html( $value ) . ';';
		}

		printf(
			'<style id="ledger-customize">:root{%s}</style>' . "\n",
			$declarations // Already escaped per-token above.
		);
	}
}
add_action( 'wp_head', 'ledger_customize_css', 20 );

/**
 * Enqueue the live-preview script in the Customizer preview frame.
 *
 * @return void
 */
if ( ! function_exists( 'ledger_customize_preview_js' ) ) {
	function ledger_customize_preview_js() {
		wp_enqueue_script(
			'ledger-customizer-preview',
			get_template_directory_uri() . '/assets/js/customizer-preview.js',
			array( 'customize-preview' ),
			wp_get_theme()->get( 'Version' ),
			true
		);
	}
}
add_action( 'customize_preview_init', 'ledger_customize_preview_js' );
