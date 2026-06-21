/**
 * Manual — motion + the signature "On this page" system.
 *
 * A calm, developer-friendly motion layer:
 *   - Gentle fade/rise reveals on scroll.
 *   - SIGNATURE: an auto-built "On this page" rail that tracks the heading
 *     currently in view (active-section tracking) and smooth-scrolls to a
 *     section when clicked — the hallmark of a documentation site.
 *   - A slim reading-progress bar on single docs.
 *   - Copy-to-clipboard buttons on code blocks.
 *
 * Principles:
 *   - Progressive enhancement. All content is visible by default. This script
 *     adds the hidden-then-reveal states only once it is safe to (JS on,
 *     IntersectionObserver available, motion allowed). If anything is missing,
 *     everything simply stays visible and readable.
 *   - Respect prefers-reduced-motion: reduce. When set, we reveal everything
 *     immediately, skip the reveal animations and smooth scroll, but still
 *     build the (informational) On-this-page rail and copy buttons.
 *   - Performance. We animate only transform & opacity, observe with a sensible
 *     rootMargin, and unobserve elements after they reveal.
 *   - Accessibility. Decorative bits are aria-hidden; focus is never moved,
 *     trapped, or hidden by motion. The rail is a real <nav> with links.
 */
( function () {
	'use strict';

	var doc = document;
	var root = doc.documentElement;

	var reduceMotion =
		window.matchMedia &&
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	var canObserve = 'IntersectionObserver' in window;

	/* ===================================================================== */
	/* Scroll reveals                                                        */
	/* ===================================================================== */

	function revealNow( el ) {
		el.classList.add( 'is-inview' );
	}

	function initReveals() {
		var nodes = doc.querySelectorAll( '[data-manual-reveal]' );
		var i;

		if ( ! canObserve ) {
			for ( i = 0; i < nodes.length; i++ ) {
				revealNow( nodes[ i ] );
			}
			return;
		}

		// Engage the motion layer FIRST. The CSS start-states (opacity:0) only
		// apply under `.manual-motion`, so adding it before we observe keeps the
		// hidden window as small as possible — and if this script never ran, the
		// class would be absent and all content would be visible.
		root.classList.add( 'manual-motion' );

		var observer = new IntersectionObserver(
			function ( entries, obs ) {
				for ( var j = 0; j < entries.length; j++ ) {
					if ( entries[ j ].isIntersecting ) {
						revealNow( entries[ j ].target );
						obs.unobserve( entries[ j ].target );
					}
				}
			},
			{ rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
		);

		// Keyboard safety: reveal a still-hidden block the moment focus enters.
		doc.addEventListener(
			'focusin',
			function ( event ) {
				var target = event.target;
				if ( ! target || ! target.closest ) {
					return;
				}
				var block = target.closest( '[data-manual-reveal]' );
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
	}

	/* ===================================================================== */
	/* SIGNATURE — "On this page" rail with active-section tracking          */
	/* ===================================================================== */

	function slugify( text, used ) {
		var base = text
			.toLowerCase()
			.replace( /[^\w\s-]/g, '' )
			.trim()
			.replace( /\s+/g, '-' )
			.replace( /-+/g, '-' );

		if ( ! base ) {
			base = 'section';
		}

		var slug = base;
		var n = 2;
		while ( used[ slug ] ) {
			slug = base + '-' + n;
			n++;
		}
		used[ slug ] = true;
		return slug;
	}

	function initOnThisPage() {
		var article = doc.querySelector( '.entry--singular .entry-content' );
		var mount = doc.querySelector( '[data-manual-toc]' );
		if ( ! article || ! mount ) {
			return;
		}

		var headings = article.querySelectorAll( 'h2, h3' );
		if ( headings.length < 2 ) {
			// Not enough structure to warrant a contents rail.
			mount.parentNode && mount.parentNode.removeChild( mount );
			return;
		}

		var used = {};
		var items = [];
		var i;

		for ( i = 0; i < headings.length; i++ ) {
			var h = headings[ i ];
			var text = ( h.textContent || '' ).trim();
			if ( ! text ) {
				continue;
			}
			if ( ! h.id ) {
				h.id = slugify( text, used );
			} else {
				used[ h.id ] = true;
			}
			items.push( {
				id: h.id,
				text: text,
				level: h.tagName === 'H3' ? 3 : 2,
				el: h,
			} );
		}

		if ( items.length < 2 ) {
			mount.parentNode && mount.parentNode.removeChild( mount );
			return;
		}

		// Build the rail markup.
		var nav = doc.createElement( 'nav' );
		nav.className = 'on-this-page on-this-page--inline';
		nav.setAttribute( 'aria-label', mount.getAttribute( 'data-manual-toc-label' ) || 'On this page' );

		var inner = doc.createElement( 'div' );
		inner.className = 'on-this-page__inner';

		var title = doc.createElement( 'p' );
		title.className = 'on-this-page__title';
		title.textContent = mount.getAttribute( 'data-manual-toc-label' ) || 'On this page';

		var list = doc.createElement( 'ul' );
		list.className = 'on-this-page__list';

		var links = {};

		for ( i = 0; i < items.length; i++ ) {
			var item = items[ i ];
			var li = doc.createElement( 'li' );
			li.className = 'on-this-page__item on-this-page__item--h' + item.level;

			var a = doc.createElement( 'a' );
			a.className = 'on-this-page__link';
			a.href = '#' + item.id;
			a.textContent = item.text;
			li.appendChild( a );
			list.appendChild( li );
			links[ item.id ] = a;

			// Smooth-scroll on click (honours reduced motion automatically via
			// CSS scroll-behavior, but we also set focus for keyboard users).
			a.addEventListener( 'click', function ( ev ) {
				var hash = this.getAttribute( 'href' );
				var targetEl = doc.getElementById( hash.slice( 1 ) );
				if ( ! targetEl ) {
					return;
				}
				ev.preventDefault();
				targetEl.scrollIntoView( {
					behavior: reduceMotion ? 'auto' : 'smooth',
					block: 'start',
				} );
				// Move focus to the heading for assistive tech without a visible
				// outline jump (headings are not normally focusable).
				targetEl.setAttribute( 'tabindex', '-1' );
				targetEl.focus( { preventScroll: true } );
				if ( history.replaceState ) {
					history.replaceState( null, '', hash );
				}
			} );
		}

		inner.appendChild( title );
		inner.appendChild( list );
		nav.appendChild( inner );
		mount.parentNode.replaceChild( nav, mount );

		// On very wide screens, lift the rail into the right gutter so it floats
		// beside the article (CSS handles the positioning when this class is on).
		function applyLayout() {
			if ( window.innerWidth >= 1376 ) {
				nav.classList.add( 'on-this-page--floating' );
			} else {
				nav.classList.remove( 'on-this-page--floating' );
			}
		}
		applyLayout();
		window.addEventListener( 'resize', applyLayout, { passive: true } );

		// Active-section tracking. We watch all headings and mark the last one
		// whose top has scrolled above the activation line as current.
		var current = null;

		function setCurrent( id ) {
			if ( id === current ) {
				return;
			}
			if ( current && links[ current ] ) {
				links[ current ].classList.remove( 'is-current' );
				links[ current ].removeAttribute( 'aria-current' );
			}
			current = id;
			if ( current && links[ current ] ) {
				links[ current ].classList.add( 'is-current' );
				links[ current ].setAttribute( 'aria-current', 'true' );
			}
		}

		if ( canObserve ) {
			// A line ~28% down the viewport is the "you are reading here" mark.
			var io = new IntersectionObserver(
				function () {
					// Recompute from scroll position for robustness across fast
					// scrolls; the observer just throttles the work to scroll.
					updateActive();
				},
				{ rootMargin: '-28% 0px -68% 0px', threshold: 0 }
			);
			for ( i = 0; i < items.length; i++ ) {
				io.observe( items[ i ].el );
			}
		}

		var ticking = false;
		function onScroll() {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( function () {
					ticking = false;
					updateActive();
				} );
			}
		}

		function updateActive() {
			var line = window.innerHeight * 0.3;
			var found = items[ 0 ].id;
			for ( var k = 0; k < items.length; k++ ) {
				var rect = items[ k ].el.getBoundingClientRect();
				if ( rect.top - line <= 1 ) {
					found = items[ k ].id;
				} else {
					break;
				}
			}
			// If we are at the very bottom, highlight the last section.
			if (
				window.innerHeight + window.pageYOffset >=
				doc.body.offsetHeight - 4
			) {
				found = items[ items.length - 1 ].id;
			}
			setCurrent( found );
		}

		window.addEventListener( 'scroll', onScroll, { passive: true } );
		updateActive();
	}

	/* ===================================================================== */
	/* Reading-progress bar                                                  */
	/* ===================================================================== */

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

	/* ===================================================================== */
	/* Code-block copy buttons                                               */
	/* ===================================================================== */

	function initCodeCopy() {
		if ( ! navigator.clipboard ) {
			return;
		}

		var pres = doc.querySelectorAll( '.entry-content pre' );
		// The translated labels are printed onto <body> in header.php, so read
		// them from there (root is <html>, which never carries these attrs).
		var labelHost = doc.body || root;
		var copyLabel = labelHost.getAttribute( 'data-manual-copy' ) || 'Copy';
		var copiedLabel = labelHost.getAttribute( 'data-manual-copied' ) || 'Copied';

		for ( var i = 0; i < pres.length; i++ ) {
			( function ( pre ) {
				// Wrap so the button can be absolutely positioned over the block.
				var wrap = doc.createElement( 'div' );
				wrap.className = 'code-block';
				pre.parentNode.insertBefore( wrap, pre );
				wrap.appendChild( pre );

				var btn = doc.createElement( 'button' );
				btn.type = 'button';
				btn.className = 'code-copy';
				btn.textContent = copyLabel;
				btn.setAttribute( 'aria-label', copyLabel );
				wrap.appendChild( btn );

				btn.addEventListener( 'click', function () {
					var code = pre.querySelector( 'code' );
					var text = ( code ? code.innerText : pre.innerText ) || '';
					navigator.clipboard.writeText( text ).then( function () {
						btn.classList.add( 'is-copied' );
						btn.textContent = copiedLabel;
						window.setTimeout( function () {
							btn.classList.remove( 'is-copied' );
							btn.textContent = copyLabel;
						}, 1600 );
					} );
				} );
			}( pres[ i ] ) );
		}
	}

	/* ===================================================================== */

	function start() {
		// Reveals are the only thing fully suppressed under reduced motion.
		if ( reduceMotion ) {
			var nodes = doc.querySelectorAll( '[data-manual-reveal]' );
			for ( var i = 0; i < nodes.length; i++ ) {
				revealNow( nodes[ i ] );
			}
		} else {
			initReveals();
		}

		// These are informational/utility and run regardless of motion pref.
		initOnThisPage();
		initReadingProgress();
		initCodeCopy();
	}

	if ( 'loading' === doc.readyState ) {
		doc.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
