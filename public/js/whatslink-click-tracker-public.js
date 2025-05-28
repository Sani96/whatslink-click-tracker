(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	document.querySelectorAll('a[href*="wa.me"], a[href*="api.whatsapp.com"]').forEach(function (el) {
		el.addEventListener('click', function () {
			fetch(whatslink_click_tracker.ajax_url, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams({
					action: 'whatslink_click_tracker_log_click',
					nonce: whatslink_click_tracker.nonce,
					url: window.location.href, 
					referrer: document.referrer || '',
					post_id: whatslink_click_tracker.post_id,
				})
			});
		});
	});

})( jQuery );
