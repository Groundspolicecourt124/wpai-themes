<?php
/**
 * Aurora Customizer: live color & style controls.
 *
 * Adds a "Colors & Style" section with Accent, Background, and Surface
 * color controls that update the whole theme live via postMessage, plus
 * selective-refresh partials for the site title and tagline.
 *
 * @package Aurora
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default values, mapped to the real CSS custom properties in style.css.
 * Keeping these in one place lets the front-end printer skip any value that
 * still matches its default, so the polished defaults render identically.
 *
 * @return array
 */
if ( ! function_exists( 'aurora_color_defaults' ) ) {
	function aurora_color_defaults() {
		return array(
			'aurora_accent'  => '#a5462a', // --a-accent
			'aurora_bg'      => '#f7f4ee', // --a-bg
			'aurora_surface' => '#ffffff', // --a-surface
		);
	}
}

/**
 * Register Customizer section, settings, controls, and partials.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
if ( ! function_exists( 'aurora_customize_register' ) ) {
	function aurora_customize_register( $wp_customize ) {
		$defaults = aurora_color_defaults();

		$wp_customize->add_section(
			'aurora_colors',
			array(
				'title'    => esc_html__( 'Colors & Style', 'aurora' ),
				'priority' => 30,
			)
		);

		// Accent color → --a-accent (and derived shades follow via color-mix).
		$wp_customize->add_setting(
			'aurora_accent',
			array(
				'default'           => $defaults['aurora_accent'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'aurora_accent',
				array(
					'label'       => esc_html__( 'Accent color', 'aurora' ),
					'description' => esc_html__( 'Links, buttons, category pills, and accents. Lighter and darker shades follow automatically.', 'aurora' ),
					'section'     => 'aurora_colors',
					'settings'    => 'aurora_accent',
				)
			)
		);

		// Background color → --a-bg (the page canvas).
		$wp_customize->add_setting(
			'aurora_bg',
			array(
				'default'           => $defaults['aurora_bg'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'aurora_bg',
				array(
					'label'       => esc_html__( 'Background color', 'aurora' ),
					'description' => esc_html__( 'The page canvas behind your content.', 'aurora' ),
					'section'     => 'aurora_colors',
					'settings'    => 'aurora_bg',
				)
			)
		);

		// Surface color → --a-surface (cards, header, sidebar panels).
		$wp_customize->add_setting(
			'aurora_surface',
			array(
				'default'           => $defaults['aurora_surface'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'aurora_surface',
				array(
					'label'       => esc_html__( 'Surface color', 'aurora' ),
					'description' => esc_html__( 'Cards, the header, and sidebar panels that sit above the background.', 'aurora' ),
					'section'     => 'aurora_colors',
					'settings'    => 'aurora_surface',
				)
			)
		);

		// Live-update the site title and tagline via selective refresh.
		if ( isset( $wp_customize->selective_refresh ) ) {
			$blogname = $wp_customize->get_setting( 'blogname' );
			if ( $blogname ) {
				$blogname->transport = 'postMessage';
			}
			$blogdescription = $wp_customize->get_setting( 'blogdescription' );
			if ( $blogdescription ) {
				$blogdescription->transport = 'postMessage';
			}

			$wp_customize->selective_refresh->add_partial(
				'blogname',
				array(
					'selector'        => '.site-title a',
					'render_callback' => 'aurora_customize_partial_blogname',
				)
			);
			$wp_customize->selective_refresh->add_partial(
				'blogdescription',
				array(
					'selector'        => '.site-description',
					'render_callback' => 'aurora_customize_partial_blogdescription',
				)
			);
		}
	}
}
add_action( 'customize_register', 'aurora_customize_register' );

/**
 * Render the site title for the selective-refresh partial.
 *
 * @return string
 */
if ( ! function_exists( 'aurora_customize_partial_blogname' ) ) {
	function aurora_customize_partial_blogname() {
		return get_bloginfo( 'name', 'display' );
	}
}

/**
 * Render the site tagline for the selective-refresh partial.
 *
 * @return string
 */
if ( ! function_exists( 'aurora_customize_partial_blogdescription' ) ) {
	function aurora_customize_partial_blogdescription() {
		return get_bloginfo( 'description', 'display' );
	}
}

/**
 * Print the chosen colors as CSS custom properties on the front end.
 * Only emits a variable when its theme mod differs from the default, so an
 * untouched site renders byte-for-byte like the original stylesheet.
 */
if ( ! function_exists( 'aurora_customize_css' ) ) {
	function aurora_customize_css() {
		$defaults = aurora_color_defaults();

		$accent  = get_theme_mod( 'aurora_accent', $defaults['aurora_accent'] );
		$bg      = get_theme_mod( 'aurora_bg', $defaults['aurora_bg'] );
		$surface = get_theme_mod( 'aurora_surface', $defaults['aurora_surface'] );

		$vars = array();

		if ( $accent && $accent !== $defaults['aurora_accent'] ) {
			$vars['--a-accent'] = $accent;
		}
		if ( $bg && $bg !== $defaults['aurora_bg'] ) {
			$vars['--a-bg'] = $bg;
		}
		if ( $surface && $surface !== $defaults['aurora_surface'] ) {
			$vars['--a-surface'] = $surface;
		}

		if ( empty( $vars ) ) {
			return;
		}

		$rules = '';
		foreach ( $vars as $name => $value ) {
			$rules .= $name . ':' . $value . ';';
		}

		printf(
			'<style id="%1$s-customize">:root{%2$s}</style>' . "\n",
			esc_attr( 'aurora' ),
			esc_html( $rules )
		);
	}
}
add_action( 'wp_head', 'aurora_customize_css', 20 );

/**
 * Enqueue the live-preview script inside the Customizer preview frame.
 */
if ( ! function_exists( 'aurora_customize_preview_js' ) ) {
	function aurora_customize_preview_js() {
		wp_enqueue_script(
			'aurora-customizer-preview',
			get_template_directory_uri() . '/assets/js/customizer-preview.js',
			array( 'customize-preview' ),
			defined( 'AURORA_VERSION' ) ? AURORA_VERSION : false,
			true
		);
	}
}
add_action( 'customize_preview_init', 'aurora_customize_preview_js' );
