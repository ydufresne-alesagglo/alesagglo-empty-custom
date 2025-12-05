<?php
/**
 * Attachment file field class
 */

namespace AlesAggloEmptyCustom;

class AttachmentField extends Field {

	protected array $allowed_types;

	public function __construct(string $meta_key, string $label, array $allowed_types = []) {
		$this->allowed_types = $allowed_types;

		parent::__construct($meta_key, $label);
	}

	public function get_allowed_types() {
		return $this->allowed_types;
	}

	public function default_render($post) {
		$meta_key = $this->get_meta_key();
		$value = get_post_meta($post->ID, $meta_key, true);

		$link = ($value ? '<a href="' . esc_url(wp_get_attachment_url($value)) . '" target="_blank">' . esc_html(basename(get_attached_file($value))) . '</a>' : '');

		$allowed_types = $this->get_allowed_types();
		$allowed_types_json = (!empty($allowed_types) ? htmlspecialchars(json_encode($allowed_types), ENT_QUOTES, 'UTF-8') : '');

		$html  = '<div class="'.parent::PREFIX.'attachment-field" data-meta-key="' . esc_attr($meta_key) . '" data-allowed-types="' . $allowed_types_json . '">';
		$html .= '<label>' . esc_html($this->get_label()) . '</label>';
		$html .= '<input type="hidden" name="' . esc_attr($meta_key) . '" id="'.parent::PREFIX.esc_attr($meta_key) . '" value="' . esc_attr($value) . '">';
		$html .= '<span class="'.parent::PREFIX.'attachment-field-link">'.$link.'</span>';
		$html .= '<button type="button" class="'.parent::PREFIX.'attachment-field-select">Select</button>';
		$html .= '<button type="button" class="'.parent::PREFIX.'attachment-field-remove">Remove</button>';
		$html .= '</div>';
		return $html;
	}
}
