/**
 * Kindred — Related Posts: motion enhancement.
 *
 * Progressive enhancement only. The cards are fully visible without this file;
 * here we add a subtle, accessible entrance (fade + slide up, in a gentle
 * stagger) when the section scrolls into view.
 *
 * Design constraints (kept in lockstep with the CSS):
 * - Animate transform + opacity ONLY (no layout properties) — no jank, no CLS.
 * - Respect prefers-reduced-motion: reduce — if set, reveal everything at once
 *   and never animate. The CSS @media block mirrors this as a safety net.
 * - Reveal on IntersectionObserver with a forgiving rootMargin; unobserve each
 *   card after it has been shown so we never re-run or hold references.
 * - No globals, no dependencies, no console noise. Defensive throughout: any
 *   missing API simply falls back to "show immediately".
 *
 * @package Kindred
 */
( function () {
	'use strict';

	var SELECTOR = '[data-kindred-card]';
	var PRIME_CLASS = 'kindred-will-reveal';
	var REVEAL_CLASS = 'kindred-is-revealed';
	var STAGGER_MS = 80; // Per-card delay so the row cascades in.
	var STAGGER_MAX = 5; // Cap the cascade so large grids don't drag.

	/**
	 * Mark a card as revealed (idempotent).
	 *
	 * @param {Element} card The card element.
	 */
	function reveal( card ) {
		card.classList.remove( PRIME_CLASS );
		card.classList.add( REVEAL_CLASS );
	}

	/**
	 * Reveal every card immediately, with no animation.
	 *
	 * Used as the universal fallback: reduced-motion, missing
	 * IntersectionObserver, or any unexpected error.
	 *
	 * @param {NodeList|Array} cards The card elements.
	 */
	function revealAll( cards ) {
		for ( var i = 0; i < cards.length; i++ ) {
			reveal( cards[ i ] );
		}
	}

	/**
	 * Wire up the scroll-reveal behavior once the DOM is ready.
	 */
	function init() {
		var cards = document.querySelectorAll( SELECTOR );

		if ( ! cards.length ) {
			return;
		}

		// Honor the user's motion preference. If reduced motion is requested —
		// or matchMedia is unavailable — skip all animation and leave the cards
		// as-is (the CSS already keeps them visible by default).
		var prefersReduced =
			typeof window.matchMedia === 'function' &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		if ( prefersReduced ) {
			return;
		}

		// Without IntersectionObserver we can't cheaply detect scroll-in, so
		// just reveal immediately rather than risk stuck-hidden cards.
		if ( typeof window.IntersectionObserver !== 'function' ) {
			revealAll( cards );
			return;
		}

		// Prime the cards into their pre-reveal (hidden) state. We do this in
		// JS — not in CSS-by-default — so that if this script never runs the
		// cards are never hidden.
		var i;
		for ( i = 0; i < cards.length; i++ ) {
			cards[ i ].classList.add( PRIME_CLASS );
		}

		// Track each card's position among the currently-visible batch so the
		// reveal cascades left-to-right rather than all at once.
		var revealedCount = 0;

		var observer = new window.IntersectionObserver(
			function ( entries, obs ) {
				// Reveal in DOM order for a tidy cascade even if the observer
				// reports entries out of order.
				entries.sort( function ( a, b ) {
					return a.target.compareDocumentPosition( b.target ) &
						Node.DOCUMENT_POSITION_FOLLOWING
						? -1
						: 1;
				} );

				for ( var j = 0; j < entries.length; j++ ) {
					var entry = entries[ j ];

					if ( entry.isIntersecting ) {
						var step = Math.min( revealedCount, STAGGER_MAX );
						entry.target.style.setProperty(
							'--kindred-delay',
							step * STAGGER_MS + 'ms'
						);
						reveal( entry.target );
						revealedCount++;
						// One-shot: never observe a revealed card again.
						obs.unobserve( entry.target );
					}
				}
			},
			{
				// Start the reveal a little before the cards are fully on
				// screen, and treat even a sliver as "in view".
				root: null,
				rootMargin: '0px 0px -8% 0px',
				threshold: 0.01,
			}
		);

		for ( i = 0; i < cards.length; i++ ) {
			observer.observe( cards[ i ] );
		}
	}

	// Run init with a guard so a throw at any point — including after cards have
	// been primed into their hidden state, and including when init runs from the
	// DOMContentLoaded listener below — reveals everything rather than leaving
	// cards stuck invisible.
	function boot() {
		try {
			init();
		} catch ( e ) {
			revealAll( document.querySelectorAll( SELECTOR ) );
		}
	}

	// Defensive bootstrap: the script is deferred, so the DOM is parsed by the
	// time it runs, but we guard anyway.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
	} else {
		boot();
	}
} )();
