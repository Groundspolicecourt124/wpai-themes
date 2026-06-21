/**
 * Monolith — Customizer live preview.
 *
 * Updates CSS custom properties and the title/tagline text in real time as
 * the user drags color pickers or edits the site identity. The derived accent
 * shades (--m-accent-hover, --m-accent-ink) are defined with color-mix() in
 * style.css, so updating --m-accent alone cascades through the whole theme.
 *
 * @package Monolith
 */
( function ( api ) {
	'use strict';

	var root = document.documentElement;

	function setVar( name, value ) {
		if ( value ) {
			root.style.setProperty( name, value );
		}
	}

	// Accent color → --m-accent (hover/ink follow via color-mix in style.css).
	api( 'monolith_accent', function ( setting ) {
		setting.bind( function ( value ) {
			setVar( '--m-accent', value );
		} );
	} );

	// Page background → --m-bg.
	api( 'monolith_bg', function ( setting ) {
		setting.bind( function ( value ) {
			setVar( '--m-bg', value );
		} );
	} );

	// Surface / panels → --m-surface.
	api( 'monolith_surface', function ( setting ) {
		setting.bind( function ( value ) {
			setVar( '--m-surface', value );
		} );
	} );

	// Site title text.
	api( 'blogname', function ( setting ) {
		setting.bind( function ( value ) {
			var el = document.querySelector( '.site-title a' );
			if ( el ) {
				el.textContent = value;
			}
		} );
	} );

	// Site tagline text.
	api( 'blogdescription', function ( setting ) {
		setting.bind( function ( value ) {
			var el = document.querySelector( '.site-description' );
			if ( el ) {
				el.textContent = value;
			}
		} );
	} );
} )( wp.customize );
