/**
 * Smooth Back to Top
 *
 * Reveals the floating button once the user has scrolled past a threshold and
 * smoothly returns to the top of the document when activated. Vanilla JS, no
 * dependencies, no external calls, and respects prefers-reduced-motion.
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

	// Read this live on each use so toggling the OS setting takes effect without
	// a reload, and degrade gracefully where matchMedia is unavailable.
	var reducedMotionQuery = window.matchMedia
		? window.matchMedia( '(prefers-reduced-motion: reduce)' )
		: { matches: false };

	var ticking = false;

	function currentScroll() {
		return (
			window.pageYOffset ||
			( document.scrollingElement && document.scrollingElement.scrollTop ) ||
			document.documentElement.scrollTop ||
			0
		);
	}

	function update() {
		ticking = false;
		button.classList.toggle( 'sbtt-visible', currentScroll() > SHOW_AFTER );
	}

	function onScroll() {
		if ( ! ticking ) {
			ticking = true;
			window.requestAnimationFrame( update );
		}
	}

	function scrollToTop() {
		var behavior = reducedMotionQuery.matches ? 'auto' : 'smooth';

		try {
			window.scrollTo( { top: 0, left: 0, behavior: behavior } );
		} catch ( e ) {
			// Older browsers without the options-object form of scrollTo().
			window.scrollTo( 0, 0 );
		}

		// Move focus to the top of the page so keyboard and screen-reader users
		// continue from there rather than from the bottom of the document.
		var target = document.querySelector( 'main' ) || document.body;
		if ( ! target || typeof target.focus !== 'function' ) {
			return;
		}

		var hadTabindex = target.hasAttribute( 'tabindex' );
		if ( ! hadTabindex ) {
			target.setAttribute( 'tabindex', '-1' );
		}

		target.focus( { preventScroll: true } );

		if ( ! hadTabindex ) {
			target.addEventListener( 'blur', function handler() {
				target.removeAttribute( 'tabindex' );
				target.removeEventListener( 'blur', handler );
			} );
		}
	}

	button.addEventListener( 'click', scrollToTop );
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', onScroll, { passive: true } );

	// Set the correct initial state (e.g. when the page loads already scrolled).
	update();
}() );
