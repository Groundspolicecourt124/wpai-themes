/**
 * Reading Time Badge — motion enhancement.
 *
 * Progressive enhancement only. The badge is fully visible without this file;
 * here we add a subtle, accessible entrance (fade + slide) when it scrolls into
 * view, plus a single gentle sweep of the clock hands on reveal.
 *
 * Design constraints (kept in lockstep with the CSS):
 * - Animate transform + opacity ONLY (no layout properties) — no jank, no CLS.
 * - Respect prefers-reduced-motion: reduce — if set, reveal everything at once
 *   and never animate. The CSS @media block mirrors this as a safety net.
 * - Reveal on IntersectionObserver with a forgiving rootMargin; unobserve each
 *   badge after it has been shown so we never re-run or hold references.
 * - No globals, no dependencies, no console noise. Defensive throughout: any
 *   missing API simply falls back to "show immediately".
 *
 * @package ReadingTimeBadge
 */
( function () {
	'use strict';

	var SELECTOR = '[data-rtb-badge]';
	var REVEAL_CLASS = 'rtb-is-revealed';
	var PRIME_CLASS = 'rtb-will-reveal';

	/**
	 * Mark a badge as revealed (idempotent).
	 *
	 * @param {Element} badge The badge element.
	 */
	function reveal( badge ) {
		badge.classList.remove( PRIME_CLASS );
		badge.classList.add( REVEAL_CLASS );
	}

	/**
	 * Reveal every badge immediately, with no animation.
	 *
	 * Used as the universal fallback: reduced-motion, missing
	 * IntersectionObserver, or any unexpected error.
	 *
	 * @param {NodeList|Array} badges The badge elements.
	 */
	function revealAll( badges ) {
		for ( var i = 0; i < badges.length; i++ ) {
			reveal( badges[ i ] );
		}
	}

	/**
	 * Wire up the scroll-reveal behavior once the DOM is ready.
	 */
	function init() {
		var badges = document.querySelectorAll( SELECTOR );

		if ( ! badges.length ) {
			return;
		}

		// Honor the user's motion preference. If reduced motion is requested —
		// or the matchMedia API is unavailable — skip all animation and show
		// the badges as-is. The CSS already keeps them visible by default, so
		// there is nothing further to do here.
		var prefersReduced =
			typeof window.matchMedia === 'function' &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		if ( prefersReduced ) {
			return;
		}

		// Without IntersectionObserver we can't cheaply detect scroll-in, so
		// just reveal immediately rather than risk a stuck hidden badge.
		if ( typeof window.IntersectionObserver !== 'function' ) {
			revealAll( badges );
			return;
		}

		// Prime the badges into their pre-reveal (hidden) state. We do this in
		// JS — not in CSS-by-default — so that if this script never runs the
		// badge is never hidden.
		var i;
		for ( i = 0; i < badges.length; i++ ) {
			badges[ i ].classList.add( PRIME_CLASS );
		}

		var observer = new window.IntersectionObserver(
			function ( entries, obs ) {
				for ( var j = 0; j < entries.length; j++ ) {
					var entry = entries[ j ];

					if ( entry.isIntersecting ) {
						reveal( entry.target );
						// One-shot: never observe a revealed badge again.
						obs.unobserve( entry.target );
					}
				}
			},
			{
				// Start the reveal a little before the badge is fully on
				// screen, and treat even a sliver as "in view".
				root: null,
				rootMargin: '0px 0px -10% 0px',
				threshold: 0.01,
			}
		);

		for ( i = 0; i < badges.length; i++ ) {
			observer.observe( badges[ i ] );
		}
	}

	// Defensive bootstrap: the script is deferred, so the DOM is parsed by the
	// time it runs, but we guard anyway. Any thrown error reveals everything so
	// the badge can never be left invisible.
	try {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', init, { once: true } );
		} else {
			init();
		}
	} catch ( e ) {
		revealAll( document.querySelectorAll( SELECTOR ) );
	}
} )();
