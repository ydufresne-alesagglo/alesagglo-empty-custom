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

	public function register($post_type) {
		register_post_meta($post_type, $this->meta_key, array(
			'type' => 'number',
			'single' => true,
			'show_in_rest' => true,
			'auth_callback' => '__return_false',
		));
	}

	public function save($post_id) {
		if (!isset($_POST[$this->meta_key])) {
			return;
		}

		$value = intval( $_POST[$this->meta_key] );
		if ($value <= 0) {
			delete_post_meta($post_id, $this->meta_key);
		} else {
			update_post_meta($post_id, $this->meta_key, $value);
		}
	}

	public function default_render($post) {
		$meta_key = $this->get_meta_key();
		$value = get_post_meta($post->ID, $meta_key, true);

		$link = ($value ? '<a href="' . esc_url(wp_get_attachment_url($value)) . '" target="_blank">' . esc_html(basename(get_attached_file($value))) . '</a>' : '&nbsp;');

		$allowed_types = $this->get_allowed_types();
		$allowed_types_json = (!empty($allowed_types) ? htmlspecialchars(json_encode($allowed_types), ENT_QUOTES, 'UTF-8') : '');

		$html = '<div class="'.parent::PREFIX.'attachment-field" data-meta-key="' . esc_attr($meta_key) . '" data-allowed-types="' . $allowed_types_json . '">';
		$html .= '<label>' . esc_html($this->get_label()) . '</label>';
		$html .= '<input type="hidden" name="' . esc_attr($meta_key) . '" id="'.parent::PREFIX.esc_attr($meta_key) . '" value="' . esc_attr($value) . '">';
		if (wp_attachment_is_image($value)) {
			$html .= '<span class="'.parent::PREFIX.'attachment-field-thumbnail"><a href="' . esc_url(wp_get_attachment_url($value)) . '" target="_blank">' . wp_get_attachment_image($value, 'medium', false) . '</a></span>';
		}
		$html .= '<span class="'.parent::PREFIX.'attachment-field-link">'.$link.'</span>';
		$html .= '<button type="button" class="'.parent::PREFIX.'attachment-field-select">Select</button>';
		$html .= '<button type="button" class="'.parent::PREFIX.'attachment-field-remove">Remove</button>';
		$html .= '</div>';
		return $html;
	}
}
