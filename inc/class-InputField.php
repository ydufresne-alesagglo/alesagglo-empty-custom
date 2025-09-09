<?php
/**
 * Input field class
 */

namespace AlesAggloEmptyCustom;

class InputField extends Field {

	private $input_type;
	private $placeholder;
	private $default_value;

	public function __construct(string $meta_key, string $label, string $input_type = 'text', string $placeholder = '', $default_value = '') {

		$allowed_types = ['text', 'number', 'url', 'email', 'password', 'checkbox', 'color', 'date'];
		if (!in_array($input_type, $allowed_types, true)) {
			$this->input_type = false;
		} else {
			$this->input_type = $input_type;
		}

		$this->placeholder = $placeholder;
		$this->default_value = $default_value;

		parent::__construct($meta_key, $label);
	}

	public function save($post_id) {
		if ($this->input_type === false) return;

		if ($this->input_type == 'checkbox') {
			if (!isset($_POST[$this->meta_key])) {
				delete_post_meta($post_id, $this->meta_key);
			} else {
				update_post_meta($post_id, $this->meta_key, 1);
			}
			return;
		}

		if (!isset($_POST[$this->meta_key])) {
			return;
		}

		$allowed = ['sup' => [], 'sub' => []];
		$value = wp_kses( wp_unslash($_POST[$this->meta_key]), $allowed );
		if ($value === '') {
			delete_post_meta($post_id, $this->meta_key);
		} else {
			update_post_meta($post_id, $this->meta_key, $value);
		}
	}

	public function render_html($post) {
		if ($this->input_type === false) return;

		$value = get_post_meta($post->ID, $this->meta_key, true);

		if ($value === '' && $this->default_value !== '') {
			$value = $this->default_value;
		}

		$html = '<div class="'.parent::PREFIX.'input-field">';
		$html .= '<label for="' . esc_attr($this->meta_key) . '">' . esc_html($this->label) . '</label>&nbsp;';
		$html .= '<input type="' . esc_attr($this->input_type) . '" name="' . esc_attr($this->meta_key) . '" id="' . esc_attr($this->meta_key) . '"';
		$html .= ($this->input_type == 'checkbox' ? ($value ? ' checked>' : '>') : ' value="' . esc_attr($value) . '" placeholder="' . esc_attr($this->placeholder) . '">');
		$html .= '</div>';

		return $html;
	}
}
