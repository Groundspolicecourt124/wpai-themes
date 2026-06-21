/**
 * Verdant Customizer live preview.
 *
 * Updates CSS custom properties (and the title/tagline text) live inside the
 * Customizer preview iframe, with no page reload.
 *
 * @package Verdant
 */
( function ( api ) {
	'use strict';

	var root = document.documentElement;

	function setVar( name, value ) {
		root.style.setProperty( name, value );
	}

	function clearVar( name ) {
		root.style.removeProperty( name );
	}

	// Accent color: drive the base var plus its directly-derived shades so the
	// whole palette cascades. Matches verdant_customize_css() in PHP.
	api( 'verdant_accent', function ( setting ) {
		setting.bind( function ( value ) {
			if ( ! value ) {
				clearVar( '--v-accent' );
				clearVar( '--v-accent-fill' );
				clearVar( '--v-accent-deep' );
				clearVar( '--v-accent-darker' );
				clearVar( '--v-accent-soft' );
				clearVar( '--v-accent-mist' );
				return;
			}
			setVar( '--v-accent', value );
			setVar( '--v-accent-fill', 'color-mix(in srgb, ' + value + ' 82%, #000)' );
			setVar( '--v-accent-deep', 'color-mix(in srgb, ' + value + ' 52%, #000)' );
			setVar( '--v-accent-darker', 'color-mix(in srgb, ' + value + ' 42%, #000)' );
			setVar( '--v-accent-soft', 'color-mix(in srgb, ' + value + ' 16%, #fff)' );
			setVar( '--v-accent-mist', 'color-mix(in srgb, ' + value + ' 10%, #fff)' );
		} );
	} );

	// Page background / paper canvas.
	api( 'verdant_bg', function ( setting ) {
		setting.bind( function ( value ) {
			if ( value ) {
				setVar( '--v-paper', value );
			} else {
				clearVar( '--v-paper' );
			}
		} );
	} );

	// Card surface color.
	api( 'verdant_surface', function ( setting ) {
		setting.bind( function ( value ) {
			if ( value ) {
				setVar( '--v-surface', value );
			} else {
				clearVar( '--v-surface' );
			}
		} );
	} );

	// Site title text.
	api( 'blogname', function ( setting ) {
		setting.bind( function ( value ) {
			document.querySelectorAll( '.site-title a' ).forEach( function ( el ) {
				el.textContent = value;
			} );
		} );
	} );

	// Site tagline text.
	api( 'blogdescription', function ( setting ) {
		setting.bind( function ( value ) {
			document.querySelectorAll( '.site-description' ).forEach( function ( el ) {
				el.textContent = value;
			} );
		} );
	} );
} )( wp.customize );
