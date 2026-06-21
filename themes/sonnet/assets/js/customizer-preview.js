/**
 * Sonnet — Customizer live preview.
 *
 * Binds each color setting to its real CSS custom property so changes in
 * Appearance → Customize repaint the whole theme instantly. The derived
 * accent shades are defined with color-mix() in style.css, so updating the
 * single --s-accent variable cascades them everywhere automatically.
 *
 * @package Sonnet
 */
( function ( api ) {
	'use strict';

	var root = document.documentElement;

	function setVar( name, value ) {
		if ( value ) {
			root.style.setProperty( name, value );
		}
	}

	// Accent color → --s-accent (links, ornament, and all derived gold shades).
	api( 'sonnet_accent', function ( setting ) {
		setting.bind( function ( value ) {
			setVar( '--s-accent', value );
		} );
	} );

	// Background color → --s-bg (the page canvas).
	api( 'sonnet_bg', function ( setting ) {
		setting.bind( function ( value ) {
			setVar( '--s-bg', value );
		} );
	} );

	// Surface color → --s-surface (cards, code blocks, footer rail).
	api( 'sonnet_surface', function ( setting ) {
		setting.bind( function ( value ) {
			setVar( '--s-surface', value );
		} );
	} );

	// Site title — update the masthead link text live.
	api( 'blogname', function ( setting ) {
		setting.bind( function ( value ) {
			var els = document.querySelectorAll( '.site-title a' );
			for ( var i = 0; i < els.length; i++ ) {
				els[ i ].textContent = value;
			}
		} );
	} );

	// Tagline — update the masthead description live.
	api( 'blogdescription', function ( setting ) {
		setting.bind( function ( value ) {
			var els = document.querySelectorAll( '.site-description' );
			for ( var i = 0; i < els.length; i++ ) {
				els[ i ].textContent = value;
			}
		} );
	} );
} )( wp.customize );
