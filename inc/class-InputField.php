<?php
require_once AEC_PATH . 'inc/class-Field.php';
/**
 * Input field class
 */
class InputField extends Field {

	private $input_type;
	private $placeholder;

	public function __construct(string $meta_key, string $label, string $input_type = 'text', string $placeholder = '') {
		$this->input_type = $input_type;
		$this->placeholder = $placeholder;

		parent::__construct($meta_key, $label);
	}

	public function save($post_id) {
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
		$value = get_post_meta($post->ID, $this->meta_key, true);

		return '<div class="'.parent::PREFIX.'input-field"><label for="' . esc_attr($this->meta_key) . '">' . esc_html($this->label) . '</label>&nbsp;' .
			'<input type="' . esc_attr($this->input_type) . '" name="' . esc_attr($this->meta_key) . '" id="' . esc_attr($this->meta_key) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($this->placeholder) . '"></div>';
	}
}
