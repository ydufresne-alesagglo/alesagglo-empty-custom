<?php
require_once AEC_PATH . 'inc/class-Field.php';

class TextareaField extends Field {

	public function __construct(string $meta_key, string $label) {
		parent::__construct($meta_key, $label);
	}

	public function save($post_id) {
		if (!isset($_POST[$this->meta_key])) {
			return;
		}

		$value = wp_kses_post( wp_unslash($_POST[$this->meta_key]) );
		if ($value == '') {
			delete_post_meta($post_id, $this->meta_key);
		} else {
			update_post_meta($post_id, $this->meta_key, $value);
		}
	}

	public function render_html($post) {
		$value = get_post_meta($post->ID, $this->meta_key, true);

		$html = '<div class="'.parent::PREFIX.'textarea-field"><label for="' . esc_attr($this->meta_key) . '">' . esc_html($this->label) . '</label><br>';
		ob_start();
		wp_editor(
			$value,
			$this->meta_key,
			array(
				'textarea_name' => $this->meta_key,
				'media_buttons' => false,
				'textarea_rows' => 10,
				'wpautop' => false,
			)
		);
		$html .= ob_get_clean().'</div>';
		return $html;
	}
}
