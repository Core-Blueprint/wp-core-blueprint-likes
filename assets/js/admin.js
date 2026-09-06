(() => {
	'use strict';

	if (window.cbLikesAdmin?.corePresentation && window.cbLikesAdmin?.settingsSaved) {
		window.addEventListener('load', () => {
			window.cbCore?.toast?.success?.(window.cbLikesAdmin.settingsSavedMessage);
		}, { once: true });
	}

	document.querySelectorAll('[data-cb-likes-icon-field]').forEach((field) => {
		const source = field.querySelector('[data-cb-likes-icon-source]');
		const builtInPanel = field.querySelector('[data-cb-likes-icon-panel="built_in"]');
		const builtInSelect = field.querySelector('[data-cb-likes-built-in-select]');
		const builtInPreview = field.querySelector('[data-cb-likes-built-in-preview]');
		const mediaPanel = field.querySelector('[data-cb-likes-icon-panel="media"]');
		const svgPanel = field.querySelector('[data-cb-likes-icon-panel="svg"]');
		const mediaId = field.querySelector('[data-cb-likes-media-id]');
		const preview = field.querySelector('[data-cb-likes-media-preview]');
		const selectButton = field.querySelector('[data-cb-likes-media-select]');
		const removeButton = field.querySelector('[data-cb-likes-media-remove]');

		const syncSource = () => {
			const value = source?.value || 'built_in';
			if (builtInPanel) builtInPanel.hidden = value !== 'built_in';
			if (mediaPanel) mediaPanel.hidden = value !== 'media';
			if (svgPanel) svgPanel.hidden = value !== 'svg';
		};

		const syncBuiltInPreview = () => {
			if (!builtInPreview || !builtInSelect) return;
			const icon = window.cbLikesAdmin?.builtInIcons?.[builtInSelect.value] || '';
			builtInPreview.innerHTML = icon;
		};

		builtInSelect?.addEventListener('change', syncBuiltInPreview);
		syncBuiltInPreview();

		const setPreview = (url = '') => {
			if (!preview) return;
			preview.replaceChildren();
			if (url) {
				const image = document.createElement('img');
				image.src = url;
				image.alt = '';
				preview.appendChild(image);
			}
			preview.hidden = !url;
			if (removeButton) removeButton.hidden = !url;
		};

		source?.addEventListener('change', syncSource);
		syncSource();

		selectButton?.addEventListener('click', (event) => {
			event.preventDefault();
			if (!window.wp?.media) return;

			const frame = window.wp.media({
				title: window.cbLikesAdmin?.mediaTitle,
				button: { text: window.cbLikesAdmin?.mediaButton },
				multiple: false,
				library: { type: 'image' },
			});

			frame.on('select', () => {
				const attachment = frame.state().get('selection').first()?.toJSON();
				if (!attachment?.id) return;
				if (mediaId) mediaId.value = String(attachment.id);
				const url = attachment.sizes?.thumbnail?.url || attachment.sizes?.medium?.url || attachment.url || '';
				setPreview(url);
			});

			frame.open();
		});

		removeButton?.addEventListener('click', (event) => {
			event.preventDefault();
			if (mediaId) mediaId.value = '0';
			setPreview('');
		});
	});

	const sections = document.querySelectorAll('[data-cb-likes-post-type]');
	if (!sections.length) {
		return;
	}

	sections.forEach((section) => {
		const collapse = section.querySelector('[data-cb-likes-post-type-collapse]');
		const body = section.querySelector('[data-cb-likes-post-type-body]');
		const toggle = section.querySelector('[data-cb-likes-post-type-toggle]');

		const setExpanded = (expanded) => {
			section.classList.toggle('is-expanded', expanded);
			if (collapse) {
				collapse.setAttribute('aria-expanded', expanded ? 'true' : 'false');
			}
			if (body) {
				body.setAttribute('aria-hidden', expanded ? 'false' : 'true');
				if (expanded) {
					body.removeAttribute('inert');
				} else {
					body.setAttribute('inert', '');
				}
			}
		};

		const syncEnabled = () => {
			if (toggle) {
				section.classList.toggle('is-enabled', toggle.checked);
			}
		};

		setExpanded(false);
		syncEnabled();

		collapse?.addEventListener('click', () => {
			setExpanded(!section.classList.contains('is-expanded'));
		});

		toggle?.addEventListener('change', syncEnabled);
	});
})();
