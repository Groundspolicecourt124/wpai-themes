/**
 * Aurora — live Customizer preview.
 *
 * Binds each color setting to its real CSS custom property so the whole
 * theme updates instantly, and refreshes the site title/tagline text.
 */
( function () {
	'use strict';

	if ( typeof wp === 'undefined' || ! wp.customize ) {
		return;
	}

	var root = document.documentElement;

	function bindColor( settingId, cssVar ) {
		wp.customize( settingId, function ( value ) {
			value.bind( function ( newValue ) {
				if ( newValue ) {
					root.style.setProperty( cssVar, newValue );
				} else {
					root.style.removeProperty( cssVar );
				}
			} );
		} );
	}

	// Accent: derived deep/soft/wash shades follow via color-mix() in CSS.
	bindColor( 'aurora_accent', '--a-accent' );
	bindColor( 'aurora_bg', '--a-bg' );
	bindColor( 'aurora_surface', '--a-surface' );

	// Site title.
	wp.customize( 'blogname', function ( value ) {
		value.bind( function ( newValue ) {
			var els = document.querySelectorAll( '.site-title a' );
			for ( var i = 0; i < els.length; i++ ) {
				els[ i ].textContent = newValue;
			}
		} );
	} );

	// Site tagline.
	wp.customize( 'blogdescription', function ( value ) {
		value.bind( function ( newValue ) {
			var els = document.querySelectorAll( '.site-description' );
			for ( var i = 0; i < els.length; i++ ) {
				els[ i ].textContent = newValue;
			}
		} );
	} );
}() );
