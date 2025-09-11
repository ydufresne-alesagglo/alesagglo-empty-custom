// scripts admin ales agglo empty custom

document.addEventListener("DOMContentLoaded", function () {

	const prefix = (aec_settings && typeof aec_settings == 'object' && aec_settings.prefix) ? aec_settings.prefix : '';

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
				link.innerHTML = `<a href="${attachment.url}">${attachment.filename}</a>`;
			});

			frame.open();
		});

		removeBtn.addEventListener('click', function (e) {
			e.preventDefault();
			inputMetaKey.value = '';
			link.innerHTML = '';
		});
	});
});
