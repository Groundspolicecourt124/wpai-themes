/**
 * Nimbus — Customizer live preview.
 *
 * Binds each color setting to its CSS custom property so changes in
 * Appearance → Customize update the whole theme instantly (no reload).
 * The derived accent shades and gradients are defined with color-mix() in
 * style.css against --n-accent / --n-accent-2, so updating those two vars
 * cascades through buttons, links, chips, the hero, and more automatically.
 */
( function () {
	'use strict';

	if ( typeof wp === 'undefined' || ! wp.customize ) {
		return;
	}

	var root = document.documentElement;

	function setVar( name, value ) {
		if ( typeof value === 'string' && value ) {
			root.style.setProperty( name, value );
		}
	}

	// Accent color → --n-accent (cascades to all derived accent shades & gradients).
	wp.customize( 'nimbus_accent', function ( setting ) {
		setting.bind( function ( value ) {
			setVar( '--n-accent', value );
		} );
	} );

	// Background color → --n-bg.
	wp.customize( 'nimbus_bg', function ( setting ) {
		setting.bind( function ( value ) {
			setVar( '--n-bg', value );
		} );
	} );

	// Secondary accent → --n-accent-2 (second stop of the gradient).
	wp.customize( 'nimbus_accent_2', function ( setting ) {
		setting.bind( function ( value ) {
			setVar( '--n-accent-2', value );
		} );
	} );

	// Site title text.
	wp.customize( 'blogname', function ( setting ) {
		setting.bind( function ( value ) {
			var el = document.querySelector( '.site-title a' );
			if ( el ) {
				el.textContent = value;
			}
		} );
	} );

	// Site description / tagline text.
	wp.customize( 'blogdescription', function ( setting ) {
		setting.bind( function ( value ) {
			var el = document.querySelector( '.site-description' );
			if ( el ) {
				el.textContent = value;
			}
		} );
	} );
} )();
