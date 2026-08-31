/**
 * Clipboard Foundation integration for Core Blueprint Likes shortcode examples.
 *
 * Loaded as a WordPress Script Module with @cb-core/clipboard as a dependency.
 * Likes owns the exact string; Base owns clipboard behavior, icon feedback,
 * toast presentation, keyboard behavior and listener cleanup.
 */

const clipboard = window.cbCore?.clipboard;

if ( clipboard?.enhance ) {
	document.querySelectorAll( '[data-cb-likes-copy-shortcode]' ).forEach( ( button ) => {
		clipboard.enhance( button, {
			text: () => button.dataset.cbLikesShortcode || '',
			label: button.dataset.cbLikesCopyLabel,
			successMessage: button.dataset.cbLikesCopySuccess,
		} );
	} );
}
