/**
 * Aurora — motion system.
 *
 * A calm, literary motion layer: gentle fade/rise reveals on scroll, a
 * word-by-word reveal on lead headlines, the signature hand-drawn ink
 * underline that draws itself, and a slim reading-progress bar on posts.
 *
 * Principles:
 *   - Progressive enhancement. All content is visible by default. This script
 *     adds the hidden-then-reveal states only once it is safe to (JS on,
 *     IntersectionObserver available, motion allowed). If anything is missing,
 *     everything simply stays visible.
 *   - Respect prefers-reduced-motion: reduce. When set, we reveal everything
 *     immediately and wire up nothing.
 *   - Performance. We animate only transform and opacity, observe with a
 *     sensible rootMargin, and unobserve each element after it reveals.
 *   - Accessibility. Decorative bits are aria-hidden; focus is never moved,
 *     trapped, or hidden by motion.
 */
( function () {
	'use strict';

	var doc = document;
	var root = doc.documentElement;

	var reduceMotion =
		window.matchMedia &&
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	var canObserve = 'IntersectionObserver' in window;

	/**
	 * Reveal an element immediately and for good (no animation state left
	 * behind). Used both as the IO callback and as the reduced-motion /
	 * no-support fallback.
	 *
	 * @param {Element} el The element to reveal.
	 */
	function revealNow( el ) {
		el.classList.add( 'is-inview' );

		// Word reveals: drop the per-word transforms so wrapping is natural.
		if ( el.hasAttribute( 'data-aurora-words' ) ) {
			el.classList.add( 'words-in' );
		}

		// Ink underline lives just after a revealed title/label.
		drawInkWithin( el.parentNode || el );
	}

	/**
	 * Kick the ink-underline draw for any underline that belongs with a
	 * revealed block. The class triggers the CSS stroke-dashoffset transition.
	 *
	 * @param {Element} scope A container to search within.
	 */
	function drawInkWithin( scope ) {
		if ( ! scope || ! scope.querySelectorAll ) {
			return;
		}
		var inks = scope.querySelectorAll( '.ink-underline' );
		for ( var i = 0; i < inks.length; i++ ) {
			inks[ i ].classList.add( 'is-drawn' );
		}
	}

	/**
	 * Split a headline's text into per-word spans so each word can rise in.
	 * We only touch the dedicated text span to avoid disturbing links or the
	 * decorative SVG sitting beside it. Whitespace is preserved so the line
	 * still wraps and reads identically to a screen reader (the original text
	 * stays intact as the concatenation of the word spans).
	 *
	 * @param {Element} host The [data-aurora-words] element.
	 */
	function splitWords( host ) {
		var target = host.querySelector(
			'.lead-essay__text, .entry-title__text'
		);

		// Fall back to the host itself only if it has no element children
		// (pure text), so we never blow away nested markup like links.
		if ( ! target ) {
			if ( host.children.length ) {
				return false;
			}
			target = host;
		}

		var text = target.textContent;
		if ( ! text || ! text.trim() ) {
			return false;
		}

		var tokens = text.split( /(\s+)/ );
		var frag = doc.createDocumentFragment();
		var delay = 0;
		var made = false;

		for ( var i = 0; i < tokens.length; i++ ) {
			var token = tokens[ i ];
			if ( '' === token ) {
				continue;
			}

			if ( /^\s+$/.test( token ) ) {
				frag.appendChild( doc.createTextNode( token ) );
				continue;
			}

			var word = doc.createElement( 'span' );
			word.className = 'a-word';
			word.textContent = token;
			// Inline custom property staggers each word; clamped so very long
			// headlines never crawl in for an uncomfortable amount of time.
			word.style.setProperty( '--w-delay', delay.toFixed( 2 ) + 's' );
			delay = Math.min( delay + 0.05, 0.7 );
			frag.appendChild( word );
			made = true;
		}

		if ( ! made ) {
			return false;
		}

		target.textContent = '';
		target.appendChild( frag );
		return true;
	}

	/**
	 * Reading-progress bar: maps scroll position through the article body to a
	 * 0..1 scaleX on the bar. rAF-throttled, transform-only, no layout reads
	 * during the animation frame beyond a cached rect.
	 */
	function initReadingProgress() {
		var wrap = doc.querySelector( '.reading-progress' );
		if ( ! wrap ) {
			return;
		}

		var bar = wrap.querySelector( '.reading-progress__bar' );
		var article = doc.querySelector( '.entry--singular' );
		if ( ! bar || ! article ) {
			return;
		}

		wrap.hidden = false;
		var ticking = false;

		function update() {
			ticking = false;

			var rect = article.getBoundingClientRect();
			var viewport = window.innerHeight || root.clientHeight;
			// Distance the article top travels from first-visible to fully read.
			var total = rect.height - viewport;
			var progress;

			if ( total <= 0 ) {
				progress = 1;
			} else {
				progress = ( -rect.top ) / total;
			}

			if ( progress < 0 ) {
				progress = 0;
			} else if ( progress > 1 ) {
				progress = 1;
			}

			bar.style.transform = 'scaleX(' + progress + ')';
		}

		function onScroll() {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( update );
			}
		}

		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', onScroll, { passive: true } );
		update();
	}

	/**
	 * Wire up all scroll reveals.
	 */
	function initReveals() {
		var nodes = doc.querySelectorAll( '[data-aurora-reveal]' );
		var words = doc.querySelectorAll( '[data-aurora-words]' );
		var i;

		if ( ! canObserve ) {
			// No IntersectionObserver: never hide anything. Split the headlines
			// (purely cosmetic) and leave every block in its visible end-state.
			for ( i = 0; i < words.length; i++ ) {
				splitWords( words[ i ] );
			}
			for ( i = 0; i < nodes.length; i++ ) {
				revealNow( nodes[ i ] );
			}
			for ( i = 0; i < words.length; i++ ) {
				revealNow( words[ i ] );
			}
			return;
		}

		// Engage the motion layer FIRST. The CSS start-states (opacity:0) only
		// apply under `.aurora-motion`, so adding it before we split or observe
		// keeps the hidden window as small as possible — and if this script had
		// never run, the class would be absent and all content would be visible.
		root.classList.add( 'aurora-motion' );

		// Prepare word headlines (split once, up front, before they reveal).
		for ( i = 0; i < words.length; i++ ) {
			if ( splitWords( words[ i ] ) ) {
				words[ i ].classList.add( 'words-ready' );
			}
		}

		var observer = new IntersectionObserver(
			function ( entries, obs ) {
				for ( var j = 0; j < entries.length; j++ ) {
					var entry = entries[ j ];
					if ( entry.isIntersecting ) {
						revealNow( entry.target );
						obs.unobserve( entry.target );
					}
				}
			},
			{
				// Reveal a touch before the element reaches the fold, and only
				// once a sliver is on screen — feels responsive, never abrupt.
				rootMargin: '0px 0px -10% 0px',
				threshold: 0.08,
			}
		);

		// Accessibility: if focus moves into a still-hidden reveal block (e.g. a
		// keyboard user tabs to an off-screen "Read the story" link before it has
		// scrolled in), reveal it permanently and stop observing it so the focus
		// ring is never sitting on an invisible element. CSS :focus-within is the
		// immediate safety net; this makes the reveal stick after focus leaves.
		doc.addEventListener(
			'focusin',
			function ( event ) {
				var target = event.target;
				if ( ! target || ! target.closest ) {
					return;
				}
				var block = target.closest( '[data-aurora-reveal], [data-aurora-words]' );
				if ( block && ! block.classList.contains( 'is-inview' ) ) {
					revealNow( block );
					observer.unobserve( block );
				}
			},
			true
		);

		for ( i = 0; i < nodes.length; i++ ) {
			observer.observe( nodes[ i ] );
		}

		// Word headlines reveal on their own observer so the stagger fires the
		// moment they enter, independent of any wrapping reveal block.
		for ( i = 0; i < words.length; i++ ) {
			// A words element may also carry data-aurora-reveal (handled above);
			// only observe standalone ones here to avoid a double reveal.
			if ( ! words[ i ].hasAttribute( 'data-aurora-reveal' ) ) {
				observer.observe( words[ i ] );
			}
		}
	}

	function start() {
		// Reduced motion (or no JS support for the basics): reveal all, wire
		// nothing animated. Reading progress is informational, so we still show
		// it but it jumps rather than eases (CSS disables its transition).
		if ( reduceMotion ) {
			var all = doc.querySelectorAll(
				'[data-aurora-reveal], [data-aurora-words]'
			);
			for ( var i = 0; i < all.length; i++ ) {
				revealNow( all[ i ] );
			}
			initReadingProgress();
			return;
		}

		initReveals();
		initReadingProgress();
	}

	if ( 'loading' === doc.readyState ) {
		doc.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
