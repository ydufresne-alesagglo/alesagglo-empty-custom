// scripts admin ales agglo empty custom

/**
 * Attachment field script
 */
document.addEventListener("DOMContentLoaded", function () {

	const prefix = (aec_settings_admin && typeof aec_settings_admin == 'object' && aec_settings_admin.prefix) ? aec_settings_admin.prefix : '';

	const fields = document.querySelectorAll('.'+prefix+'attachment-field');

	fields.forEach(field => {
		const metaKey = field.getAttribute('data-meta-key');
		let allowedTypes = field.getAttribute('data-allowed-types');

		const inputMetaKey = field.querySelector('#'+prefix+metaKey);
		const link = field.querySelector('.'+prefix+'attachment-field-link');
		const selectBtn = field.querySelector('.'+prefix+'attachment-field-select');
		const removeBtn = field.querySelector('.'+prefix+'attachment-field-remove');

		let types = null;
		if (allowedTypes) {
			try {
				const parsedAllowedTypes = JSON.parse(allowedTypes);
				if (Array.isArray(parsedAllowedTypes) && parsedAllowedTypes.length > 0) {
					types = parsedAllowedTypes;
				}
			} catch (e) {
				console.log('Erreur data-types pour ', metaKey);
			}
		}

		let frame = null;
		selectBtn.addEventListener('click', function (e) {
			e.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: 'Sélectionner un média',
				button: { text: 'Utiliser ce média' },
				multiple: false,
				library: (types ? { type: types } : {}),
			});

			frame.on('select', function () {
				const attachment = frame.state().get('selection').first().toJSON();
				inputMetaKey.value = attachment.id;
				link.innerHTML = `<a href="${attachment.url}" target="_blank">${attachment.filename}</a>`;
			});

			frame.open();
		});

		removeBtn.addEventListener('click', function (e) {
			e.preventDefault();
			inputMetaKey.value = '';
			link.innerHTML = '&nbsp;';
		});
	});
});


/**
 * Post field script
 */
document.addEventListener("DOMContentLoaded", function () {

	const prefix = (aec_settings_admin && typeof aec_settings_admin == 'object' && aec_settings_admin.prefix) ? aec_settings_admin.prefix : '';

	const fields = document.querySelectorAll('.'+prefix+'post-field');

	fields.forEach(field => {
		const metaKey = field.getAttribute('data-meta-key');
		const postType = field.getAttribute('data-post-type');

		const inputMetaKey = field.querySelector('#'+prefix+metaKey);
		const link = field.querySelector('.'+prefix+'post-field-link');
		const selectBtn = field.querySelector('.'+prefix+'post-field-select');
		const removeBtn = field.querySelector('.'+prefix+'post-field-remove');
		const closeBtn = field.querySelector('.' + prefix + 'post-search-close');
		const container = field.querySelector('.' + prefix + 'post-search-container');
		const search = field.querySelector('.' + prefix + 'post-search-input');
		const results = field.querySelector('.' + prefix + 'post-search-results');

		selectBtn.addEventListener('click', function (e) {
			e.preventDefault();
			const isVisible = container.style.display === 'block';
			container.style.display = isVisible ? 'none' : 'block';
			if (!isVisible) { // visibility just changed to visible
				search.focus();
			} else {
				results.innerHTML = '';
				search.value = '';
			}
		});

		removeBtn.addEventListener('click', function (e) {
			e.preventDefault();
			inputMetaKey.value = '';
			link.innerHTML = '&nbsp;';
			container.style.display = 'none';
			results.innerHTML = '';
			search.value = '';
		});

		closeBtn.addEventListener('click', function (e) {
			e.preventDefault();
			container.style.display = 'none';
			results.innerHTML = '';
			search.value = '';
		});

		let timer;

		search.addEventListener('input', function () {
			clearTimeout(timer);
			const term = search.value.trim();
			if (term.length < 3) {
				results.innerHTML = '';
				return;
			}

			timer = setTimeout(async () => {
				const url = `/wp/v2/${postType}?search=${encodeURIComponent(term)}&search_columns=post_title&orderby=modified&order=desc&status[]=publish&status[]=future&per_page=12`;
				try {
					const data = await wp.apiFetch({ path: url });
					results.innerHTML = '';

					if (data.length === 0) {
						const li = document.createElement('li');
						li.textContent = 'Aucun résultat';
						li.style.cursor = 'default';
						results.appendChild(li);
						return;
					}

					data.forEach(post => {
						const li = document.createElement('li');
						li.textContent = post.title.rendered || '(sans titre)';
						li.dataset.id = post.id;
						results.appendChild(li);
					});
				} catch (err) {
					console.error('Erreur recherche via API :', err);
				}
			}, 500);
		});

		results.addEventListener('click', function (e) {
			if (e.target.tagName !== 'LI' || !e.target.dataset.id) return;

			const postId = e.target.dataset.id;
			const postTitle = e.target.textContent;

			inputMetaKey.value = postId;
			link.innerHTML = `<a href="${window.location.origin}/?p=${postId}" target="_blank">${postTitle}</a>`;
			container.style.display = 'none';
			results.innerHTML = '';
			search.value = '';
		});
	});
});
