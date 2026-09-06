(() => {
	'use strict';

	const enhance = () => {
		const clipboard = window.cbCore?.clipboard;
		if (!clipboard?.enhance) return;

		document.querySelectorAll('[data-cb-likes-copy-shortcode]').forEach((button) => {
			clipboard.enhance(button, {
				text: () => button.dataset.cbLikesShortcode || '',
				label: button.dataset.cbLikesCopyLabel,
				successMessage: button.dataset.cbLikesCopySuccess,
			});
		});
	};

	if (document.readyState === 'complete') enhance();
	else window.addEventListener('load', enhance, { once: true });
})();
