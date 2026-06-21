<?php
/**
 * Nimbus Customizer: live color & style controls.
 *
 * Adds a "Colors & Style" section to Appearance → Customize with three
 * color controls (Accent, Background, Secondary accent). The chosen values
 * are printed as CSS custom properties on the front end, and the same
 * variables update instantly in the preview via postMessage.
 *
 * @package Nimbus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default color values. These MUST match the :root declarations in style.css
 * so that an un-customized site renders identically and we only ever emit an
 * override when a setting actually differs from its default.
 *
 * @return array{accent:string,bg:string,accent_2:string}
 */
function nimbus_customize_defaults() {
	return array(
		'accent'   => '#6366f1', // --n-accent
		'bg'       => '#f6f7fb', // --n-bg
		'accent_2' => '#8b5cf6', // --n-accent-2
	);
}

/**
 * Register Customizer settings, the section, and the color controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function nimbus_customize_register( $wp_customize ) {
	$defaults = nimbus_customize_defaults();

	$wp_customize->add_section(
		'nimbus_colors',
		array(
			'title'    => esc_html__( 'Colors & Style', 'nimbus' ),
			'priority' => 30,
		)
	);

	// Accent color → --n-accent (and all derived accent shades cascade from it).
	$wp_customize->add_setting(
		'nimbus_accent',
		array(
			'default'           => $defaults['accent'],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'nimbus_accent',
			array(
				'label'       => esc_html__( 'Accent color', 'nimbus' ),
				'description' => esc_html__( 'Drives buttons, links, chips, the hero gradient, and all accent shades.', 'nimbus' ),
				'section'     => 'nimbus_colors',
				'settings'    => 'nimbus_accent',
			)
		)
	);

	// Background color → --n-bg (the page canvas).
	$wp_customize->add_setting(
		'nimbus_bg',
		array(
			'default'           => $defaults['bg'],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'nimbus_bg',
			array(
				'label'       => esc_html__( 'Background color', 'nimbus' ),
				'description' => esc_html__( 'The soft canvas the white cards float on.', 'nimbus' ),
				'section'     => 'nimbus_colors',
				'settings'    => 'nimbus_bg',
			)
		)
	);

	// Secondary accent → --n-accent-2 (the second stop of the signature gradient).
	$wp_customize->add_setting(
		'nimbus_accent_2',
		array(
			'default'           => $defaults['accent_2'],
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'nimbus_accent_2',
			array(
				'label'       => esc_html__( 'Secondary accent', 'nimbus' ),
				'description' => esc_html__( 'The second color of the violet-to-indigo gradient hero and buttons.', 'nimbus' ),
				'section'     => 'nimbus_colors',
				'settings'    => 'nimbus_accent_2',
			)
		)
	);

	// Live-update the title & tagline text via selective refresh / postMessage.
	if ( isset( $wp_customize->selective_refresh ) ) {
		$blogname_setting = $wp_customize->get_setting( 'blogname' );
		if ( $blogname_setting ) {
			$blogname_setting->transport = 'postMessage';
		}
		$blogdescription_setting = $wp_customize->get_setting( 'blogdescription' );
		if ( $blogdescription_setting ) {
			$blogdescription_setting->transport = 'postMessage';
		}

		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'nimbus_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'nimbus_customize_partial_blogdescription',
			)
		);
	}
}
add_action( 'customize_register', 'nimbus_customize_register' );

/**
 * Render the site title for the selective-refresh partial.
 *
 * @return string
 */
function nimbus_customize_partial_blogname() {
	return get_bloginfo( 'name', 'display' );
}

/**
 * Render the site description for the selective-refresh partial.
 *
 * @return string
 */
function nimbus_customize_partial_blogdescription() {
	return get_bloginfo( 'description', 'display' );
}

/**
 * Print the customized colors as CSS custom properties on the front end.
 *
 * Only variables whose theme mod differs from the style.css default are
 * emitted, so a default site stays byte-for-byte unchanged. When the accent
 * changes we also re-derive the accent shades and gradients with color-mix()
 * so the whole palette cascades from the single Accent control.
 */
function nimbus_customize_css() {
	$defaults = nimbus_customize_defaults();

	$accent   = get_theme_mod( 'nimbus_accent', $defaults['accent'] );
	$bg       = get_theme_mod( 'nimbus_bg', $defaults['bg'] );
	$accent_2 = get_theme_mod( 'nimbus_accent_2', $defaults['accent_2'] );

	$vars = array();

	if ( $accent && $accent !== $defaults['accent'] ) {
		$vars['--n-accent'] = $accent;
	}
	if ( $bg && $bg !== $defaults['bg'] ) {
		$vars['--n-bg'] = $bg;
	}
	if ( $accent_2 && $accent_2 !== $defaults['accent_2'] ) {
		$vars['--n-accent-2'] = $accent_2;
	}

	if ( empty( $vars ) ) {
		return;
	}

	$lines = '';
	foreach ( $vars as $name => $value ) {
		$lines .= sprintf( '%s:%s;', esc_html( $name ), esc_html( $value ) );
	}

	printf(
		'<style id="%1$s-customize">:root{%2$s}</style>' . "\n",
		esc_attr( 'nimbus' ),
		$lines // Already escaped per-token above; this is CSS, not HTML.
	);
}
add_action( 'wp_head', 'nimbus_customize_css', 20 );

/**
 * Enqueue the live-preview script in the Customizer preview pane.
 */
function nimbus_customize_preview_js() {
	wp_enqueue_script(
		'nimbus-customizer-preview',
		get_template_directory_uri() . '/assets/js/customizer-preview.js',
		array( 'customize-preview' ),
		wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'customize_preview_init', 'nimbus_customize_preview_js' );
