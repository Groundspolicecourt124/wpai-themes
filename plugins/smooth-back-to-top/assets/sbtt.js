/**
 * Smooth Back to Top
 *
 * Reveals the floating button once the user has scrolled past a threshold and
 * smoothly returns to the top of the document when activated. Vanilla JS, no
 * dependencies, respects prefers-reduced-motion.
 */
( function () {
	'use strict';

	var SHOW_AFTER = 400; // Pixels scrolled before the button appears.

	var button = document.querySelector( '.sbtt-button' );
	if ( ! button ) {
		return;
	}

	// The button ships hidden so it never flashes before JS runs; reveal it now
	// that we can manage its visibility.
	button.removeAttribute( 'hidden' );

	var prefersReducedMotion = window.matchMedia
		? window.matchMedia( '(prefers-reduced-motion: reduce)' )
		: { matches: false };

	var ticking = false;

	function currentScroll() {
		return window.pageYOffset || document.documentElement.scrollTop || 0;
	}

	function update() {
		ticking = false;
		if ( currentScroll() > SHOW_AFTER ) {
			button.classList.add( 'sbtt-visible' );
		} else {
			button.classList.remove( 'sbtt-visible' );
		}
	}

	function onScroll() {
		if ( ! ticking ) {
			ticking = true;
			window.requestAnimationFrame( update );
		}
	}

	function scrollToTop() {
		var behavior = prefersReducedMotion.matches ? 'auto' : 'smooth';

		try {
			window.scrollTo( { top: 0, left: 0, behavior: behavior } );
		} catch ( e ) {
			// Older browsers without smooth-scroll support.
			window.scrollTo( 0, 0 );
		}

		// Move focus to the top of the page for keyboard/screen-reader users.
		var target = document.querySelector( 'main' ) || document.body;
		if ( target && typeof target.focus === 'function' ) {
			var hadTabindex = target.hasAttribute( 'tabindex' );
			if ( ! hadTabindex ) {
				target.setAttribute( 'tabindex', '-1' );
			}
			target.focus( { preventScroll: true } );
			if ( ! hadTabindex ) {
				target.addEventListener(
					'blur',
					function handler() {
						target.removeAttribute( 'tabindex' );
						target.removeEventListener( 'blur', handler );
					}
				);
			}
		}
	}

	button.addEventListener( 'click', scrollToTop );
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', onScroll, { passive: true } );

	// Set the correct initial state (e.g. when the page loads already scrolled).
	update();
} )();
