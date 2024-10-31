/* @license magnet:?xt=urn:btih:1f739d935676111cfff4b4693e3816e664797050&dn=gpl-3.0.txt GPL-v3-or-Later */
	
/** 
 *  This script displays modal window.
 * 
 *  Script - modal_window.js 
 * 
 *	Copyright (C) electric-fire
 */


(function() {
	
	// Function called to open the window:
	function openModal() {
		'use strict';

		// Add a click handler to the open modal button:
		document.getElementById('efmw_closeModal').onclick = closeModal;

		// Make the modal DIV visible:
		document.getElementById('efmw_modal').style.opacity = 1;
		document.body.className += ' efmw_active';

		window.addEventListener('keydown', function(evt) {
				if (parseInt(evt.keyCode, 10) === 27) {
					closeModal();
				}
		});

	} // End of openModal() function.



	// Function called to close the window:
	function closeModal() {
		'use strict';

		// For debugging:
		// console.log('efmw_obj.delay_time: ' + efmw_obj.delay_time);
		// console.log('efmw_obj.fade_out_duration: ' + efmw_obj.fade_out_duration);

		// Make the modal DIV invisible:
		document.getElementById('efmw_modal').style.opacity = 0;

		// Remove the click handler on the close modal button:
		document.getElementById('efmw_closeModal').onclick = null;

		/* Remove the modal window after CSS transition
		 * is completed (default: 1s, used dynamic value instead), 
		 * otherwise it will block access
		 * to the links.
		 */
		setTimeout(function() {
			var modal = document.getElementById('efmw_modal');
			modal.parentElement.removeChild(modal);
		}, efmw_obj.fade_out_duration);

		//document.body.style.overflow = 'visible';

		if (document.body.classList) {
			document.body.classList.remove('efmw_active');
		} else {
			document.body.className = document.body.className.replace(/efmw_active/, '');
		}

	} // End of closeModal() function.



	// Establish functionality on window load:
	document.addEventListener('DOMContentLoaded', function() {
		'use strict';

		setTimeout( function() {

			openModal();

		}, efmw_obj.delay_time );

	});

})();


/* @license-end */
