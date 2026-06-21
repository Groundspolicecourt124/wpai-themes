<?php
/**
 * Verdant Customizer: live color & style controls.
 *
 * Lets a non-technical user recolor the whole theme from
 * Appearance > Customize > Colors & Style, with instant live preview.
 *
 * @package Verdant
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default color values. These mirror the hex values in style.css :root so the
 * theme renders identically until the user changes something.
 *
 * @return array<string,string>
 */
if ( ! function_exists( 'verdant_color_defaults' ) ) {
	function verdant_color_defaults() {
		return array(
			'accent'  => '#5f9265', // --v-accent (base sage)
			'bg'      => '#f3f6ef', // --v-paper (page canvas)
			'surface' => '#ffffff', // --v-surface (card surface)
		);
	}
}

/**
 * Register Customizer settings, the "Colors & Style" section, and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
if ( ! function_exists( 'verdant_customize_register' ) ) {
	function verdant_customize_register( $wp_customize ) {
		$defaults = verdant_color_defaults();

		// Make the title & tagline update live (postMessage + selective refresh).
		$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

		if ( isset( $wp_customize->selective_refresh ) ) {
			$wp_customize->selective_refresh->add_partial( 'blogname', array(
				'selector'        => '.site-title a',
				'render_callback' => 'verdant_partial_blogname',
			) );
			$wp_customize->selective_refresh->add_partial( 'blogdescription', array(
				'selector'        => '.site-description',
				'render_callback' => 'verdant_partial_blogdescription',
			) );
		}

		// Section.
		$wp_customize->add_section( 'verdant_colors', array(
			'title'    => esc_html__( 'Colors & Style', 'verdant' ),
			'priority' => 30,
		) );

		// Accent color.
		$wp_customize->add_setting( 'verdant_accent', array(
			'default'           => $defaults['accent'],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'verdant_accent', array(
			'label'       => esc_html__( 'Accent color', 'verdant' ),
			'description' => esc_html__( 'The main green used for links, buttons, pills, and dividers. Lighter and darker shades follow it automatically.', 'verdant' ),
			'section'     => 'verdant_colors',
			'settings'    => 'verdant_accent',
		) ) );

		// Background (page canvas) color.
		$wp_customize->add_setting( 'verdant_bg', array(
			'default'           => $defaults['bg'],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'verdant_bg', array(
			'label'       => esc_html__( 'Background color', 'verdant' ),
			'description' => esc_html__( 'The soft paper canvas behind the whole page.', 'verdant' ),
			'section'     => 'verdant_colors',
			'settings'    => 'verdant_bg',
		) ) );

		// Surface (card) color.
		$wp_customize->add_setting( 'verdant_surface', array(
			'default'           => $defaults['surface'],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'verdant_surface', array(
			'label'       => esc_html__( 'Card surface color', 'verdant' ),
			'description' => esc_html__( 'The color of cards, the sticky widget area, and other raised surfaces.', 'verdant' ),
			'section'     => 'verdant_colors',
			'settings'    => 'verdant_surface',
		) ) );
	}
}
add_action( 'customize_register', 'verdant_customize_register' );

/**
 * Selective-refresh render callback for the site title.
 *
 * @return void
 */
if ( ! function_exists( 'verdant_partial_blogname' ) ) {
	function verdant_partial_blogname() {
		bloginfo( 'name' );
	}
}

/**
 * Selective-refresh render callback for the site tagline.
 *
 * @return void
 */
if ( ! function_exists( 'verdant_partial_blogdescription' ) ) {
	function verdant_partial_blogdescription() {
		bloginfo( 'description' );
	}
}

/**
 * Print customized CSS variables on the front end.
 *
 * Only emits a variable when its saved value differs from the default, so an
 * un-customized site renders byte-for-byte identically to the stylesheet's
 * :root defaults. When the accent changes, the directly-derived accent shades
 * are recomputed with color-mix() so the new accent cascades everywhere.
 *
 * @return void
 */
if ( ! function_exists( 'verdant_customize_css' ) ) {
	function verdant_customize_css() {
		$defaults = verdant_color_defaults();

		$accent  = get_theme_mod( 'verdant_accent', $defaults['accent'] );
		$bg      = get_theme_mod( 'verdant_bg', $defaults['bg'] );
		$surface = get_theme_mod( 'verdant_surface', $defaults['surface'] );

		$rules = array();

		if ( $accent && strtolower( $accent ) !== strtolower( $defaults['accent'] ) ) {
			$accent = sanitize_hex_color( $accent );
			if ( $accent ) {
				$rules[] = '--v-accent:' . $accent . ';';
				// Directly-derived accent shades follow the base accent.
				$rules[] = '--v-accent-fill:color-mix(in srgb, ' . $accent . ' 82%, #000);';
				$rules[] = '--v-accent-deep:color-mix(in srgb, ' . $accent . ' 52%, #000);';
				$rules[] = '--v-accent-darker:color-mix(in srgb, ' . $accent . ' 42%, #000);';
				$rules[] = '--v-accent-soft:color-mix(in srgb, ' . $accent . ' 16%, #fff);';
				$rules[] = '--v-accent-mist:color-mix(in srgb, ' . $accent . ' 10%, #fff);';
			}
		}

		if ( $bg && strtolower( $bg ) !== strtolower( $defaults['bg'] ) ) {
			$bg = sanitize_hex_color( $bg );
			if ( $bg ) {
				$rules[] = '--v-paper:' . $bg . ';';
			}
		}

		if ( $surface && strtolower( $surface ) !== strtolower( $defaults['surface'] ) ) {
			$surface = sanitize_hex_color( $surface );
			if ( $surface ) {
				$rules[] = '--v-surface:' . $surface . ';';
			}
		}

		if ( empty( $rules ) ) {
			return;
		}

		echo '<style id="verdant-customize">:root{' . esc_html( implode( '', $rules ) ) . '}</style>' . "\n";
	}
}
add_action( 'wp_head', 'verdant_customize_css', 20 );

/**
 * Enqueue the live-preview script inside the Customizer preview iframe.
 *
 * @return void
 */
if ( ! function_exists( 'verdant_customize_preview_js' ) ) {
	function verdant_customize_preview_js() {
		wp_enqueue_script(
			'verdant-customizer-preview',
			get_template_directory_uri() . '/assets/js/customizer-preview.js',
			array( 'customize-preview' ),
			wp_get_theme()->get( 'Version' ),
			true
		);
	}
}
add_action( 'customize_preview_init', 'verdant_customize_preview_js' );
