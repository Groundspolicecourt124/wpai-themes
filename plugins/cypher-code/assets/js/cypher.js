/**
 * Cypher — Code Blocks (behavior layer).
 *
 * Progressive enhancement for the wrapped <pre><code> blocks. Everything here is
 * optional polish: with this file absent, blocked, or erroring, the code block
 * is still tidy, scrollable, theme-adaptive, and shows its language label — only
 * the copy button and the exact line-number ordinals are added by this script.
 *
 * What it adds:
 *   - Copy to clipboard: clicking the (CSS-hidden-until-now) copy button copies
 *     the block's raw code text, flips the button to a confirmed state, and
 *     announces the result through an adjacent role="status" aria-live region.
 *     Uses the async Clipboard API where available with a resilient
 *     execCommand('copy') fallback; the confirmed state reverts after a moment.
 *   - Accurate line numbers: for blocks marked --numbered, each source line is
 *     wrapped in a <span class="cypher-line"> so the pure-CSS counter in the
 *     stylesheet renders correct, right-aligned ordinals in the reserved gutter.
 *     The code text is never modified — only wrapped — so copy still yields the
 *     original source exactly.
 *
 * Constraints, kept in lockstep with the CSS:
 *   - The only motion is the copy button's compositor-friendly color/border state
 *     transition (no layout-affecting properties are animated); prefers-reduced-
 *     motion is read live here and also enforced by the stylesheet's @media block.
 *   - No globals, no dependencies, no network, no console noise. Defensive
 *     throughout: any missing API simply degrades to the no-JS presentation.
 *
 * @package Cypher
 */
( function () {
	'use strict';

	var blocks = Array.prototype.slice.call(
		document.querySelectorAll( '[data-cypher]' )
	);
	if ( ! blocks.length ) {
		return;
	}

	// Live reduced-motion query so toggling the OS setting needs no reload;
	// degrade gracefully where matchMedia is unavailable.
	var reducedMotionQuery =
		typeof window.matchMedia === 'function'
			? window.matchMedia( '(prefers-reduced-motion: reduce)' )
			: { matches: false };

	function prefersReducedMotion() {
		return !! reducedMotionQuery.matches;
	}

	/* -----------------------------------------------------------------------
	 * Line numbering: wrap each source line in a span so the CSS counter can
	 * paint accurate ordinals. We read the code element's text, split on
	 * newlines, and rebuild it as line spans. Done once per block; we never
	 * touch the text content itself (copy still returns the exact source).
	 * --------------------------------------------------------------------- */
	function numberLines( block ) {
		var code = block.querySelector( '.cypher-pre > code, .cypher-pre code' );
		if ( ! code || code.getAttribute( 'data-cypher-lined' ) === 'true' ) {
			return;
		}

		// Use textContent so we get the raw source without any nested markup
		// surprises; this also means highlighted markup (if a theme added any)
		// would be flattened — so we only number when the code is plain text.
		// Detect plain text by comparing childNodes: a single text node is safe.
		var onlyText =
			code.childNodes.length === 0 ||
			( code.childNodes.length === 1 &&
				code.childNodes[ 0 ].nodeType === 3 );

		if ( ! onlyText ) {
			// Pre-highlighted or structured code: skip numbering rather than
			// risk destroying existing markup. The reserved gutter + rule still
			// read as an intentional margin, so nothing looks broken.
			return;
		}

		var rawText = code.textContent;

		// Stash the exact original source before we re-wrap the lines. Dropping the
		// conventional trailing newline below changes the rebuilt textContent, so
		// without this the copy action would lose that newline — readCode() prefers
		// this stash to keep copy byte-for-byte faithful to the source.
		try {
			code.cypherRawText = rawText;
		} catch ( err ) {}

		var text = rawText;

		// Drop a single trailing newline so we don't render an empty final line
		// number for the conventional trailing \n in fenced code.
		if ( text.charAt( text.length - 1 ) === '\n' ) {
			text = text.slice( 0, -1 );
		}

		var lines = text.split( '\n' );
		var frag = document.createDocumentFragment();

		for ( var i = 0; i < lines.length; i++ ) {
			var span = document.createElement( 'span' );
			span.className = 'cypher-line';
			// Preserve empty lines with their height by giving them content the
			// layout can measure; a newline keeps `white-space: pre` honest.
			span.textContent = lines[ i ] + ( i < lines.length - 1 ? '\n' : '' );
			frag.appendChild( span );
		}

		// Replace the code's contents with the line-wrapped fragment in one shot.
		code.textContent = '';
		code.appendChild( frag );
		code.setAttribute( 'data-cypher-lined', 'true' );
	}

	/* -----------------------------------------------------------------------
	 * Copy to clipboard.
	 * --------------------------------------------------------------------- */
	function readCode( block ) {
		var code = block.querySelector( '.cypher-pre > code, .cypher-pre code' );
		if ( ! code ) {
			return '';
		}
		// Prefer the original source stashed before line-wrapping (numberLines drops
		// a trailing newline from the rebuilt DOM); fall back to textContent for
		// blocks that were never numbered, where it is already the exact source.
		if ( typeof code.cypherRawText === 'string' ) {
			return code.cypherRawText;
		}
		return code.textContent;
	}

	function copyViaExecCommand( text ) {
		// Resilient fallback for browsers without the async Clipboard API or
		// when it rejects (e.g. insecure context). Uses a hidden textarea.
		var area = document.createElement( 'textarea' );
		area.value = text;
		area.setAttribute( 'readonly', '' );
		area.style.position = 'absolute';
		area.style.left = '-9999px';
		area.style.top = '0';
		document.body.appendChild( area );

		var selected =
			document.getSelection && document.getSelection().rangeCount > 0
				? document.getSelection().getRangeAt( 0 )
				: null;

		area.select();

		var ok = false;
		try {
			ok = document.execCommand( 'copy' );
		} catch ( err ) {
			ok = false;
		}

		document.body.removeChild( area );

		// Restore any prior selection we clobbered.
		if ( selected && document.getSelection ) {
			document.getSelection().removeAllRanges();
			document.getSelection().addRange( selected );
		}

		return ok;
	}

	function copyText( text ) {
		// Prefer the async Clipboard API in a secure context; otherwise fall
		// back. Always resolves to a boolean success so callers stay simple.
		if (
			navigator.clipboard &&
			typeof navigator.clipboard.writeText === 'function' &&
			window.isSecureContext !== false
		) {
			return navigator.clipboard.writeText( text ).then(
				function () {
					return true;
				},
				function () {
					return copyViaExecCommand( text );
				}
			);
		}

		return Promise.resolve( copyViaExecCommand( text ) );
	}

	function setConfirmed( button, status, confirmed ) {
		var labelEl = button.querySelector( '[data-cypher-copy-label]' );
		var idle = labelEl ? labelEl.getAttribute( 'data-label-idle' ) : '';
		var done = labelEl ? labelEl.getAttribute( 'data-label-done' ) : '';

		if ( confirmed ) {
			button.setAttribute( 'data-copied', 'true' );
			if ( labelEl && done ) {
				labelEl.textContent = done;
			}
			if ( status && done ) {
				// Announce to assistive technology via the live region.
				status.textContent = done;
			}
		} else {
			button.removeAttribute( 'data-copied' );
			if ( labelEl && idle ) {
				labelEl.textContent = idle;
			}
			if ( status ) {
				status.textContent = '';
			}
		}
	}

	function wireCopy( block ) {
		var button = block.querySelector( '[data-cypher-copy]' );
		if ( ! button ) {
			return;
		}

		var status = block.querySelector( '.cypher-block__status' );
		var revertTimer = null;

		button.addEventListener( 'click', function () {
			var text = readCode( block );

			Promise.resolve( copyText( text ) ).then( function ( ok ) {
				if ( ! ok ) {
					// Copy failed (e.g. permission denied). Stay quiet — no
					// console noise — and leave the button in its idle state so
					// the user can select the code manually as they always could.
					return;
				}

				setConfirmed( button, status, true );

				if ( revertTimer ) {
					window.clearTimeout( revertTimer );
				}
				// Revert the confirmed state after a moment. With reduced motion
				// the CSS transition is already disabled, so this just snaps.
				revertTimer = window.setTimeout(
					function () {
						setConfirmed( button, status, false );
					},
					prefersReducedMotion() ? 1200 : 1800
				);
			} );
		} );
	}

	/* -----------------------------------------------------------------------
	 * Initialize every block.
	 * --------------------------------------------------------------------- */
	blocks.forEach( function ( block ) {
		try {
			if ( block.classList.contains( 'cypher-block--numbered' ) ) {
				numberLines( block );
			}
			wireCopy( block );
		} catch ( err ) {
			// One bad block never takes down the rest; the block degrades to its
			// no-JS presentation (tidy, scrollable, labelled).
		}
	} );
}() );
