/**
 * Relay — Social Share (behavior layer).
 *
 * Progressive enhancement for the share bar. Everything here is optional polish:
 * with this file absent, blocked, or erroring, the X / Bluesky / LinkedIn / Email
 * links still work as plain anchors. Only the two client-side actions — Copy link
 * and Mastodon (which has no single host) — need JavaScript, and both degrade
 * sensibly.
 *
 * What it adds:
 *   - Copy link: writes the post URL to the clipboard and shows a brief,
 *     accessible "Copied!" confirmation (announced via an aria-live region). Uses
 *     the async Clipboard API where available, with a hidden-textarea fallback,
 *     and a final fallback that selects the URL and asks the user to press Ctrl+C.
 *   - Mastodon: prompts for the visitor's instance (there is no central host),
 *     remembers it for the session, and opens that instance's share intent.
 *
 * Constraints, kept in lockstep with the CSS:
 *   - Animate transform / opacity only; the confirmation cross-fades in place
 *     with zero layout shift. prefers-reduced-motion is honored (the CSS @media
 *     block removes movement; this script keeps timing minimal regardless).
 *   - No globals, no dependencies, no network beyond the share-intent window the
 *     user explicitly opens. No console noise. Defensive throughout.
 *
 * @package RelayShare
 */
( function () {
	'use strict';

	/**
	 * Localized strings + config injected by wp_localize_script. Falls back to
	 * English defaults so the script is robust even if localization is missing.
	 */
	var I18N =
		typeof window.relayShareI18n === 'object' && window.relayShareI18n
			? window.relayShareI18n
			: {};

	var COPIED = I18N.copied || 'Copied!';
	var COPY_FAILED = I18N.copyFailed || 'Press Ctrl+C to copy';
	var MASTODON_PROMPT =
		I18N.mastodonPrompt ||
		'Enter your Mastodon instance (e.g. mastodon.social):';
	var CONFIRM_MS = parseInt( I18N.confirmDuration, 10 ) || 2200;

	var STORAGE_KEY = 'relayMastodonInstance';

	/**
	 * Whether the visitor has asked the OS/browser to reduce motion.
	 *
	 * The CSS @media (prefers-reduced-motion: reduce) block already strips every
	 * transform/transition, so the visual layer is safe on its own. This JS guard
	 * mirrors that preference at the behavior layer: any movement this script could
	 * introduce (e.g. the transform used to center the manual-copy fallback) is
	 * skipped when motion is reduced. Robust if matchMedia is unavailable.
	 *
	 * @return {boolean} True when reduced motion is preferred.
	 */
	function prefersReducedMotion() {
		try {
			return (
				typeof window.matchMedia === 'function' &&
				window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches
			);
		} catch ( err ) {
			return false;
		}
	}

	/**
	 * Write text to the clipboard, resolving true on success.
	 *
	 * Tries the async Clipboard API first (needs a secure context + permission),
	 * then a hidden-textarea + execCommand fallback for older / non-secure pages.
	 *
	 * @param {string} text The text to copy.
	 * @return {Promise<boolean>} Resolves true if the copy succeeded.
	 */
	function copyText( text ) {
		// Preferred path: async Clipboard API.
		if (
			navigator.clipboard &&
			typeof navigator.clipboard.writeText === 'function'
		) {
			return navigator.clipboard.writeText( text ).then(
				function () {
					return true;
				},
				function () {
					return legacyCopy( text );
				}
			);
		}

		return Promise.resolve( legacyCopy( text ) );
	}

	/**
	 * Hidden-textarea + execCommand copy fallback.
	 *
	 * @param {string} text The text to copy.
	 * @return {boolean} Whether the copy command reported success.
	 */
	function legacyCopy( text ) {
		var ta = document.createElement( 'textarea' );
		ta.value = text;
		// Keep it out of view and out of the layout / tab order.
		ta.setAttribute( 'readonly', '' );
		ta.setAttribute( 'aria-hidden', 'true' );
		ta.style.position = 'fixed';
		ta.style.top = '-9999px';
		ta.style.left = '-9999px';
		ta.style.opacity = '0';
		document.body.appendChild( ta );

		var ok = false;
		try {
			ta.focus();
			ta.select();
			ta.setSelectionRange( 0, ta.value.length );
			ok = document.execCommand( 'copy' );
		} catch ( err ) {
			ok = false;
		}

		document.body.removeChild( ta );
		return !! ok;
	}

	/**
	 * Show the "Copied!" (or failure) confirmation on a copy button.
	 *
	 * Toggles a data attribute the CSS uses to cross-fade the status region in,
	 * updates the aria-live status text so screen readers announce it, then
	 * clears it after a short delay. Each press resets the timer so rapid clicks
	 * don't flicker.
	 *
	 * @param {Element} btn     The copy button.
	 * @param {boolean} success Whether the copy succeeded.
	 */
	function showConfirmation( btn, success ) {
		var status = btn.querySelector( '[data-relay-status]' );
		var message = success ? COPIED : COPY_FAILED;

		if ( status ) {
			status.textContent = message;
		}

		btn.setAttribute( 'data-relay-copied', 'true' );

		// Reset any pending hide so the latest press wins.
		if ( btn._relayTimer ) {
			window.clearTimeout( btn._relayTimer );
		}

		btn._relayTimer = window.setTimeout( function () {
			btn.removeAttribute( 'data-relay-copied' );
			// Clear the live region so the next copy writes a genuine change that
			// the aria-live region re-announces (re-setting the same text would
			// not reliably be announced by screen readers).
			if ( status ) {
				status.textContent = '';
			}
			btn._relayTimer = null;
		}, CONFIRM_MS );
	}

	/**
	 * Handle a Copy-link button activation.
	 *
	 * @param {Element} btn The copy button.
	 */
	function handleCopy( btn ) {
		var url = btn.getAttribute( 'data-relay-url' );
		if ( ! url ) {
			return;
		}

		copyText( url ).then( function ( ok ) {
			showConfirmation( btn, ok );

			// Last-resort affordance: if the copy failed, select the URL in a
			// transient field so the user can copy it manually.
			if ( ! ok ) {
				manualSelectFallback( url );
			}
		} );
	}

	/**
	 * Place the URL in a temporary, selected, on-screen field as a final manual
	 * fallback when programmatic copy is blocked. The field removes itself on
	 * blur so the page stays clean.
	 *
	 * @param {string} url The URL to expose for manual copying.
	 */
	function manualSelectFallback( url ) {
		try {
			var input = document.createElement( 'input' );
			input.type = 'text';
			input.value = url;
			input.setAttribute( 'aria-label', url );
			input.style.position = 'fixed';
			input.style.top = '1rem';
			input.style.zIndex = '99999';
			input.style.padding = '0.5rem';
			input.style.maxWidth = '90vw';
			// Center horizontally. Honor reduced motion: use a plain offset rather
			// than a transform so nothing relies on the transform/animation layer.
			if ( prefersReducedMotion() ) {
				input.style.left = '5vw';
				input.style.right = '5vw';
				input.style.margin = '0 auto';
			} else {
				input.style.left = '50%';
				input.style.transform = 'translateX(-50%)';
			}
			document.body.appendChild( input );
			input.focus();
			input.select();

			input.addEventListener( 'blur', function () {
				if ( input.parentNode ) {
					input.parentNode.removeChild( input );
				}
			} );
		} catch ( err ) {
			// Nothing more we can safely do.
		}
	}

	/**
	 * Normalize a user-entered Mastodon instance into a bare host.
	 *
	 * Strips any scheme, leading @, path, and surrounding whitespace, and rejects
	 * anything that doesn't look like a host (must contain a dot, no spaces).
	 *
	 * @param {string} raw The raw prompt input.
	 * @return {string} A clean host, or '' if invalid.
	 */
	function normalizeInstance( raw ) {
		if ( ! raw ) {
			return '';
		}

		var host = String( raw ).trim();
		host = host.replace( /^https?:\/\//i, '' ); // Drop scheme.
		host = host.replace( /^@/, '' ); // Drop a leading @.
		host = host.split( '/' )[ 0 ]; // Drop any path.
		host = host.replace( /\s+/g, '' ); // No internal whitespace.

		// A plausible host: has a dot, only host-safe characters.
		if ( ! /^[a-z0-9.-]+\.[a-z]{2,}$/i.test( host ) ) {
			return '';
		}

		return host.toLowerCase();
	}

	/**
	 * Handle a Mastodon button activation: prompt for the instance, then open its
	 * share intent in a new tab. The instance is remembered for the session so
	 * the visitor is asked at most once.
	 *
	 * @param {Element} btn The mastodon button.
	 */
	function handleMastodon( btn ) {
		var url = btn.getAttribute( 'data-relay-url' );
		var title = btn.getAttribute( 'data-relay-title' ) || '';
		if ( ! url ) {
			return;
		}

		var saved = '';
		try {
			saved = window.sessionStorage.getItem( STORAGE_KEY ) || '';
		} catch ( err ) {
			saved = '';
		}

		var entered = window.prompt( MASTODON_PROMPT, saved );
		if ( null === entered ) {
			return; // User cancelled.
		}

		var host = normalizeInstance( entered );
		if ( ! host ) {
			return; // Invalid input; do nothing rather than open a bad URL.
		}

		try {
			window.sessionStorage.setItem( STORAGE_KEY, host );
		} catch ( err ) {
			// Session storage unavailable (e.g. private mode); not fatal.
		}

		var text = ( title ? title + ' ' : '' ) + url;
		var shareUrl =
			'https://' +
			host +
			'/share?text=' +
			encodeURIComponent( text );

		var win = window.open( shareUrl, '_blank', 'noopener,noreferrer' );
		if ( win ) {
			win.opener = null;
		}
	}

	/**
	 * Wire up the client-side controls once the DOM is ready.
	 */
	function init() {
		var buttons = document.querySelectorAll( '[data-relay-action]' );
		if ( ! buttons.length ) {
			return;
		}

		for ( var i = 0; i < buttons.length; i++ ) {
			( function ( btn ) {
				var action = btn.getAttribute( 'data-relay-action' );

				btn.addEventListener( 'click', function () {
					if ( 'copy' === action ) {
						handleCopy( btn );
					} else if ( 'mastodon' === action ) {
						handleMastodon( btn );
					}
				} );
			} )( buttons[ i ] );
		}
	}

	// Defensive bootstrap: the script is deferred, so the DOM is parsed by the
	// time it runs, but we guard anyway. Any thrown error is swallowed so the
	// plain share links keep working.
	try {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', init, { once: true } );
		} else {
			init();
		}
	} catch ( e ) {
		// No-op: links still function without this enhancement.
	}
} )();
